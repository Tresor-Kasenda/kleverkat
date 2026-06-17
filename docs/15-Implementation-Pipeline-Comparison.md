# Implémentation du pipeline de comparaison

## Principe général

```
Questionnaire → Éligibilité (filtre) → Scoring (pondération) → Pricing (ajustement) → Classement (tri)
```

Chaque étape est un évaluateur indépendant. Le `ComparisonService` orchestre le pipeline.

---

## 1. Migrations nécessaires

### offer_rules

```php
Schema::create('offer_rules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('question_id')->constrained()->cascadeOnDelete();
    $table->string('rule_type');               // eligibility | scoring | pricing
    $table->string('operator');                // eq, neq, gt, gte, lt, lte, in, not_in, between
    $table->string('expected_value')->nullable();
    $table->string('expected_value_max')->nullable(); // pour between
    $table->integer('weight')->default(1);     // poids de la règle dans le scoring
    $table->decimal('score_delta', 8, 2)->default(0)->nullable();   // points ajoutés/retirés au score
    $table->decimal('price_delta', 12, 2)->default(0)->nullable();  // montant fixe ajouté/retiré
    $table->decimal('price_multiplier', 8, 4)->default(1)->nullable(); // coefficient multiplicateur
    $table->integer('priority')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### comparison_sessions

```php
Schema::create('comparison_sessions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignId('product_type_id')->constrained()->cascadeOnDelete();
    $table->foreignId('questionnaire_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->json('answers');
    $table->timestamp('started_at')->useCurrent();
    $table->timestamp('completed_at')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamps();
});
```

### comparison_results

```php
Schema::create('comparison_results', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('comparison_session_id')->constrained()->cascadeOnDelete();
    $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->boolean('is_eligible')->default(false);
    $table->decimal('score', 8, 2)->nullable();
    $table->decimal('calculated_price', 12, 2)->nullable();
    $table->json('explanation')->nullable();   // pourquoi ce score/prix
    $table->unsignedInteger('rank')->nullable();
    $table->timestamps();
});
```

---

## 2. Models

### OfferRule

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'offer_id', 'question_id', 'rule_type', 'operator',
    'expected_value', 'expected_value_max', 'weight',
    'score_delta', 'price_delta', 'price_multiplier',
    'priority', 'is_active',
])]
class OfferRule extends Model
{
    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'score_delta' => 'decimal:2',
            'price_delta' => 'decimal:2',
            'price_multiplier' => 'decimal:4',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
```

### ComparisonSession

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComparisonSession extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ComparisonResult::class);
    }
}
```

### ComparisonResult

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComparisonResult extends Model
{
    protected function casts(): array
    {
        return [
            'is_eligible' => 'boolean',
            'score' => 'decimal:2',
            'calculated_price' => 'decimal:2',
            'explanation' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ComparisonSession::class, 'comparison_session_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
```

---

## 3. Le service de comparaison

```php
<?php

namespace App\Services\Comparison;

use App\Models\Offer;
use App\Models\ComparisonSession;
use App\Models\ComparisonResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ComparisonService
{
    public function __construct(
        private EligibilityEvaluator $eligibility,
        private ScoringEvaluator $scoring,
        private PricingEvaluator $pricing,
    ) {}

    /**
     * @param  array<string, mixed>  $answers  Réponses au questionnaire
     * @param  Collection<int, Offer>  $offers  Offres à évaluer
     */
    public function evaluate(
        array $answers,
        Collection $offers,
        int $productTypeId,
        int $questionnaireId,
        ?int $userId = null,
    ): ComparisonSession {
        $session = ComparisonSession::create([
            'id' => Str::uuid(),
            'product_type_id' => $productTypeId,
            'questionnaire_id' => $questionnaireId,
            'user_id' => $userId,
            'answers' => $answers,
        ]);

        $evaluated = $offers
            ->map(fn (Offer $offer) => $this->evaluateOffer($offer, $answers))
            ->filter(fn (OfferEvaluation $evaluation) => $evaluation->eligible)
            ->sortByDesc('score')
            ->values();

        $rank = 1;
        foreach ($evaluated as $evaluation) {
            $session->results()->create([
                'offer_id' => $evaluation->offer->id,
                'company_id' => $evaluation->offer->company_id,
                'is_eligible' => $evaluation->eligible,
                'score' => $evaluation->score,
                'calculated_price' => $evaluation->price,
                'explanation' => $evaluation->explanation,
                'rank' => $rank++,
            ]);
        }

        $session->update(['completed_at' => now()]);

        return $session->load('results.offer.company');
    }

    private function evaluateOffer(Offer $offer, array $answers): OfferEvaluation
    {
        $rules = $offer->activeRules->groupBy('rule_type');

        $eligible = $this->eligibility->evaluate($offer, $answers, $rules->get('eligibility', collect()));
        if (! $eligible) {
            return new OfferEvaluation($offer, false, 0, null, ['reason' => 'Non éligible']);
        }

        $score = $this->scoring->evaluate($offer, $answers, $rules->get('scoring', collect()));
        $price = $this->pricing->evaluate($offer, $answers, $rules->get('pricing', collect()));

        return new OfferEvaluation($offer, true, $score, $price, [
            'score' => $score,
            'price' => $price,
            'rules_applied' => $rules->flatten()->count(),
        ]);
    }
}
```

### Value Object

```php
<?php

namespace App\Services\Comparison;

use App\Models\Offer;

class OfferEvaluation
{
    public function __construct(
        public Offer $offer,
        public bool $eligible,
        public float $score = 0,
        public ?float $price = null,
        public array $explanation = [],
    ) {}
}
```

---

## 4. Évaluateur d'éligibilité

Filtre les offres selon les règles `rule_type = eligibility`.

Chaque règle compare la réponse utilisateur à `expected_value` via l'opérateur. Si une seule règle est violée → offre non éligible.

```php
<?php

namespace App\Services\Comparison;

use App\Models\Offer;
use Illuminate\Support\Collection;

class EligibilityEvaluator
{
    /**
     * @param  array<string, mixed>  $answers
     * @param  Collection<int, \App\Models\OfferRule>  $rules
     */
    public function evaluate(Offer $offer, array $answers, Collection $rules): bool
    {
        if ($rules->isEmpty()) {
            return true; // pas de règles = éligible par défaut
        }

        foreach ($rules as $rule) {
            $actual = $answers[$rule->question->field_key] ?? null;

            if (! $this->matches($actual, $rule->operator, $rule->expected_value)) {
                return false;
            }
        }

        return true;
    }

    private function matches(mixed $actual, string $operator, ?string $expected): bool
    {
        return match ($operator) {
            'eq'   => (string) $actual === (string) $expected,
            'neq'  => (string) $actual !== (string) $expected,
            'gt'   => is_numeric($actual) && $actual > (float) $expected,
            'gte'  => is_numeric($actual) && $actual >= (float) $expected,
            'lt'   => is_numeric($actual) && $actual < (float) $expected,
            'lte'  => is_numeric($actual) && $actual <= (float) $expected,
            'in'   => $expected && in_array((string) $actual, explode(',', $expected), true),
            default => true,
        };
    }
}
```

---

## 5. Évaluateur de scoring

Calcule un score d'adéquation **0-100** pour chaque offre.

```
score = Σ(poids × score_delta) / Σ(poids_max_possible) × 100
```

```php
<?php

namespace App\Services\Comparison;

use App\Models\Offer;
use Illuminate\Support\Collection;

class ScoringEvaluator
{
    /**
     * @param  array<string, mixed>  $answers
     * @param  Collection<int, \App\Models\OfferRule>  $rules
     */
    public function evaluate(Offer $offer, array $answers, Collection $rules): float
    {
        if ($rules->isEmpty()) {
            return 50.0; // score neutre si pas de règles
        }

        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($rules as $rule) {
            $actual = $answers[$rule->question->field_key] ?? null;
            $weight = $rule->weight ?: 1;

            $totalWeight += $weight;

            if ($this->conditionMet($actual, $rule->operator, $rule->expected_value)) {
                $weightedSum += $weight * ($rule->score_delta ?: 0);
            }
        }

        if ($totalWeight === 0) {
            return 50.0;
        }

        // moyenne pondérée normalisée sur 100
        $rawScore = $weightedSum / $totalWeight;

        return round(max(0, min(100, 50 + $rawScore)), 2);
    }

    private function conditionMet(mixed $actual, string $operator, ?string $expected): bool
    {
        return match ($operator) {
            'eq'   => (string) $actual === (string) $expected,
            'neq'  => (string) $actual !== (string) $expected,
            'gt'   => is_numeric($actual) && $actual > (float) $expected,
            'gte'  => is_numeric($actual) && $actual >= (float) $expected,
            'lt'   => is_numeric($actual) && $actual < (float) $expected,
            'lte'  => is_numeric($actual) && $actual <= (float) $expected,
            'in'   => $expected && in_array((string) $actual, explode(',', $expected), true),
            default => false,
        };
    }
}
```

---

## 6. Évaluateur de pricing

Calcule le prix final à partir du prix de base et des règles de l'offre.

```
price = base_price × Π(multipliers) + Σ(deltas)
```

```php
<?php

namespace App\Services\Comparison;

use App\Models\Offer;
use Illuminate\Support\Collection;

class PricingEvaluator
{
    /**
     * @param  array<string, mixed>  $answers
     * @param  Collection<int, \App\Models\OfferRule>  $rules
     */
    public function evaluate(Offer $offer, array $answers, Collection $rules): ?float
    {
        $price = $offer->base_price;

        if ($price === null) {
            return null; // sur devis
        }

        foreach ($rules as $rule) {
            $actual = $answers[$rule->question->field_key] ?? null;

            if (! $this->conditionMet($actual, $rule->operator, $rule->expected_value)) {
                continue;
            }

            if ($rule->price_multiplier !== null) {
                $price *= $rule->price_multiplier;
            }

            if ($rule->price_delta !== null) {
                $price += $rule->price_delta;
            }
        }

        return round(max(0, $price), 2);
    }

    private function conditionMet(mixed $actual, string $operator, ?string $expected): bool
    {
        return match ($operator) {
            'eq'   => (string) $actual === (string) $expected,
            'neq'  => (string) $actual !== (string) $expected,
            'gt'   => is_numeric($actual) && $actual > (float) $expected,
            'gte'  => is_numeric($actual) && $actual >= (float) $expected,
            'lt'   => is_numeric($actual) && $actual < (float) $expected,
            'lte'  => is_numeric($actual) && $actual <= (float) $expected,
            'in'   => $expected && in_array((string) $actual, explode(',', $expected), true),
            default => false,
        };
    }
}
```

---

## 7. Exemple d'utilisation

```php
// Dans un Livewire component ou un controller :

use App\Models\Offer;
use App\Services\Comparison\ComparisonService;

$answers = [
    'age' => '28',
    'vehicle_usage' => 'personnel',
    'city' => 'paris',
    'bonus' => '0.50',
];

$offers = Offer::query()
    ->where('product_type_id', $productTypeId)
    ->where('is_active', true)
    ->with('activeRules.question')
    ->get();

$session = app(ComparisonService::class)->evaluate(
    answers: $answers,
    offers: $offers,
    productTypeId: $productTypeId,
    questionnaireId: $questionnaireId,
    userId: auth()->id(),
);

// $session->results est trié par score descendant
foreach ($session->results as $result) {
    echo "{$result->offer->name}: score={$result->score}, prix={$result->calculated_price}";
}
```

---

## 8. Exemple de données en base

### offer_rules pour "Allianz Auto Confort"

| offer_id | question.field_key | rule_type | operator | expected_value | weight | score_delta | price_multiplier |
|----------|-------------------|-----------|----------|---------------|--------|-------------|-----------------|
| 1 | age | eligibility | lt | 18 | — | — | — |
| 1 | age | scoring | gte | 25 | 3 | +5 | — |
| 1 | vehicle_usage | scoring | eq | professionnel | 2 | +10 | — |
| 1 | city | pricing | eq | paris | — | — | 1.15 |
| 1 | bonus | pricing | gt | 0.50 | — | — | 0.95 |

### Interprétation

1. **Éligibilité** : Si âge < 18, l'offre est exclue.
2. **Scoring** : Si âge >= 25, +5 points (poids 3). Si usage professionnel, +10 points (poids 2). Score max possible = (3×5 + 2×10)/5 = 7 → 50 + 7 = 57/100.
3. **Pricing** : Paris → prix × 1.15. Bonus > 0.50 → prix × 0.95.

---

## 9. Points clés

- **Un évaluateur, une responsabilité** : Chaque classe fait une seule chose. Facile à tester, modifier, remplacer.
- **Règles en base** : Les partenaires peuvent modifier leurs propres règles via l'espace partenaire. Pas de déploiement nécessaire.
- **Score neutre par défaut** : 50/100. Les règles ajoutent ou retirent des points au cas par cas.
- **Explications stockées** : `explanation` JSON sur `comparison_results` pour le débogage et l'affichage.
- **Pas d'appels API** : MVP sans dépendance externe. Les prix sont calculés à partir des données en base.
- **Prêt pour ML plus tard** : Il suffit de remplacer `ScoringEvaluator` par un appel à un modèle.

---

*Document généré le 17 juin 2026 — basé sur l'analyse des docs internes et l'architecture existante.*
