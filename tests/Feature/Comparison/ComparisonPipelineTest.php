<?php

/**
 * Comparison pipeline — real products, real companies, real market prices.
 *
 * 10 products × 10 companies = 100 companies total.
 * Each product's questionnaire has 11 questions covering every QuestionInputType:
 *   Text, Number, Select, Radio, Checkbox, Date, Boolean, Textarea.
 *
 * NOTE: The public Livewire wizard (category → sector → product → answers → results)
 * does not exist yet. These tests validate the service layer that will power it.
 */

use App\Enums\OfferRuleOperator;
use App\Enums\OfferRuleType;
use App\Enums\QuestionInputType;
use App\Filament\Pages\CategoryPage;
use App\Filament\Pages\SectorPage;
use App\Models\Category;
use App\Models\Company;
use App\Models\ComparisonResult;
use App\Models\ComparisonSession;
use App\Models\Offer;
use App\Models\OfferRule;
use App\Models\Product;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\Sector;
use App\Models\User;
use App\Services\Comparison\ComparisonService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Livewire;

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Create one question attached to the given questionnaire.
 *
 * @param  array<string, mixed>|null  $options  key-value pairs for select/radio/checkbox
 */
function q(
    Questionnaire $questionnaire,
    string $fieldKey,
    string $label,
    QuestionInputType $type,
    int $sort,
    ?array $options = null,
    string $step = 'informations',
    bool $required = true,
): Question {
    return Question::factory()->for($questionnaire)->create([
        'field_key' => $fieldKey,
        'label' => $label,
        'input_type' => $type,
        'options_json' => $options,
        'sort_order' => $sort,
        'step_key' => $step,
        'is_required' => $required,
        'is_active' => true,
    ]);
}

/**
 * Create a company + one active offer for the given product.
 */
function mkOffer(Category $category, Product $product, string $company, string $offer, float $price): Offer
{
    return Offer::factory()
        ->for(Company::factory()->for($category)->create([
            'name' => $company,
            'slug' => Str::slug($company).'-'.Str::random(4),
            'is_active' => true,
        ]))
        ->for($product)
        ->create([
            'name' => $offer,
            'slug' => Str::slug($offer).'-'.Str::random(4),
            'base_price' => $price,
            'is_active' => true,
        ]);
}

/**
 * Run the comparison engine and return eligible results ordered by price ascending.
 *
 * @return Collection<int, ComparisonResult>
 */
function compare(ComparisonSession $session): Collection
{
    $service = app(ComparisonService::class);
    $service->compare($session);

    return $service->rankedByPrice($session);
}

// ─── Test 1 — Admin creates catalogue via Filament ───────────────────────────

describe('Admin — création du catalogue via les pages Filament', function () {

    it("crée une catégorie 'Assurance & Protection' puis un secteur 'Assurance Voyage'", function () {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(CategoryPage::class)
            ->call('mountAction', 'create')
            ->set([
                'mountedActions.0.data.name' => 'Assurance & Protection',
                'mountedActions.0.data.slug' => 'assurance-protection',
                'mountedActions.0.data.description' => 'Produits de couverture des personnes, biens et responsabilités.',
                'mountedActions.0.data.sort_order' => 1,
                'mountedActions.0.data.is_active' => true,
            ])
            ->call('callMountedAction')
            ->assertHasNoFormErrors();

        $category = Category::where('slug', 'assurance-protection')->firstOrFail();

        Livewire::test(SectorPage::class)
            ->call('mountAction', 'create')
            ->set([
                'mountedActions.0.data.category_id' => $category->id,
                'mountedActions.0.data.name' => 'Assurance Voyage',
                'mountedActions.0.data.slug' => 'assurance-voyage',
                'mountedActions.0.data.description' => 'Couverture médicale et annulation pour les voyages en France et à l\'étranger.',
                'mountedActions.0.data.sort_order' => 1,
                'mountedActions.0.data.is_active' => true,
            ])
            ->call('callMountedAction')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Sector::class, [
            'category_id' => $category->id,
            'name' => 'Assurance Voyage',
        ]);
    });

    it('crée le catalogue complet puis compare des offres assurance voyage', function () {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        // Hierarchy
        $category = Category::factory()->create(['name' => 'Assurance & Protection', 'slug' => 'ap-admin-test']);
        $sector = Sector::factory()->for($category)->create(['name' => 'Assurance Voyage', 'slug' => 'av-admin-test']);
        $product = Product::factory()->for($sector)->create([
            'name' => 'Assurance Voyage Individuelle',
            'slug' => 'avi-admin-test',
            'is_active' => true,
        ]);

        // Questionnaire
        $questionnaire = Questionnaire::factory()->for($product)->create(['name' => 'Questionnaire Voyage', 'is_active' => true]);
        q($questionnaire, 'destination', 'Destination du voyage', QuestionInputType::Select, 1,
            ['europe' => 'Europe', 'monde' => 'Monde entier', 'dom_tom' => 'DOM-TOM'], 'voyage');
        q($questionnaire, 'duree_jours', 'Durée du séjour (jours)', QuestionInputType::Number, 2, step: 'voyage');

        // 3 companies — admin creates them
        $offers = [
            mkOffer($category, $product, 'AXA Travel', 'AXA Voyage Confort Europe', 19.50),
            mkOffer($category, $product, 'Chapka Assurance', 'Chapka Cap Travel', 13.90),
            mkOffer($category, $product, 'Allianz Travel', 'Allianz Assistance Europe', 22.00),
        ];

        // User simulation
        $session = ComparisonSession::factory()->create([
            'product_id' => $product->id,
            'questionnaire_id' => $questionnaire->id,
            'answers_json' => ['destination' => 'europe', 'duree_jours' => '14'],
        ]);

        $results = compare($session);

        expect($results)->toHaveCount(3)
            ->and((float) $results->first()->calculated_price)->toBe(13.90)  // Chapka cheapest
            ->and((float) $results->last()->calculated_price)->toBe(22.00)   // Allianz most expensive
            ->and($session->fresh()->isCompleted())->toBeTrue();
    });
});

// ─── Produit 1 — Assurance Voyage Individuelle ───────────────────────────────
// 10 compagnies, prix marché Europe 2 semaines 1 adulte.
// Aucune règle d'éligibilité : toutes les offres sont accessibles.

describe('Produit 1 — Assurance Voyage Individuelle (AXA, Allianz, Chapka, April…)', function () {

    beforeEach(function () {
        $category = Category::factory()->create(['name' => 'Assurance & Protection', 'slug' => 'ap-voyage']);
        $sector = Sector::factory()->for($category)->create(['name' => 'Assurance Voyage', 'slug' => 'sect-voyage']);

        $this->product = Product::factory()->for($sector)->create([
            'name' => 'Assurance Voyage Individuelle',
            'slug' => 'assurance-voyage-individuelle',
            'is_active' => true,
        ]);

        $this->questionnaire = Questionnaire::factory()->for($this->product)->create([
            'name' => 'Questionnaire Assurance Voyage',
            'is_active' => true,
        ]);

        // 11 questions — tous les types représentés
        q($this->questionnaire, 'destination', 'Quelle est votre destination ?', QuestionInputType::Select, 1,
            ['europe' => 'Europe', 'monde' => 'Monde entier', 'dom_tom' => 'DOM-TOM', 'ameriques' => 'Amériques', 'asie' => 'Asie-Pacifique', 'afrique' => 'Afrique'], 'voyage');

        q($this->questionnaire, 'date_depart', 'Date de départ', QuestionInputType::Date, 2, step: 'voyage');

        q($this->questionnaire, 'date_retour', 'Date de retour prévue', QuestionInputType::Date, 3, step: 'voyage');

        q($this->questionnaire, 'nombre_voyageurs', 'Nombre de voyageurs', QuestionInputType::Number, 4, step: 'voyage');

        q($this->questionnaire, 'age_souscripteur', 'Âge du souscripteur principal', QuestionInputType::Number, 5, step: 'profil');

        q($this->questionnaire, 'motif_voyage', 'Motif principal du voyage', QuestionInputType::Radio, 6,
            ['tourisme' => 'Tourisme / Loisirs', 'affaires' => 'Affaires', 'etudes' => 'Études', 'sport' => 'Stage sportif'], 'voyage');

        q($this->questionnaire, 'sports_risque', 'Pratiquerez-vous des sports à risque ?', QuestionInputType::Boolean, 7, step: 'couverture');

        q($this->questionnaire, 'detail_sports', 'Décrivez les activités sportives prévues', QuestionInputType::Textarea, 8, step: 'couverture', required: false);

        q($this->questionnaire, 'capital_medical', 'Capital médical souhaité', QuestionInputType::Select, 9,
            ['cap_30000' => '30 000 €', 'cap_75000' => '75 000 €', 'cap_150000' => '150 000 €', 'cap_300000' => '300 000 €'], 'couverture');

        q($this->questionnaire, 'options_couverture', 'Options souhaitées', QuestionInputType::Checkbox, 10,
            ['annulation' => 'Annulation voyage', 'bagages' => 'Perte / vol bagages', 'rc' => 'Responsabilité civile', 'rapatriement' => 'Rapatriement médical'], 'couverture');

        q($this->questionnaire, 'nom_souscripteur', 'Nom et prénom du souscripteur', QuestionInputType::Text, 11, step: 'identite');

        // 10 compagnies — prix marché réel (Europe, 14 jours, 1 adulte 35 ans)
        $this->offers = collect([
            ['ACS Assurance', 'ACS Globe Partner Europe', 11.50],
            ['Chapka Assurance', 'Chapka Cap Travel Europe', 13.90],
            ['Covertrip', 'Covertrip Sérénité Europe', 15.50],
            ['April International', 'April Voyage Europe Confort', 16.80],
            ['Mondial Assistance', 'Mondial Voyage Europe', 18.00],
            ['AXA Travel', 'AXA Voyage Confort Europe', 19.50],
            ['Europ Assistance', 'Europ Assistance Voyage Plus', 21.50],
            ['Allianz Travel', 'Allianz Assistance Europe', 22.00],
            ['Amaguiz', 'Amaguiz Voyage Premium', 24.90],
            ['Groupama', 'Groupama Assistance Voyage', 17.20],
        ])->map(fn ($c) => mkOffer($category, $this->product, $c[0], $c[1], $c[2]));
    });

    it('retourne 10 offres éligibles classées du moins cher au plus cher', function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => [
                'destination' => 'europe',
                'date_depart' => '2026-07-15',
                'date_retour' => '2026-07-29',
                'nombre_voyageurs' => '1',
                'age_souscripteur' => '35',
                'motif_voyage' => 'tourisme',
                'sports_risque' => 'non',
                'detail_sports' => '',
                'capital_medical' => '150000',
                'options_couverture' => ['annulation', 'bagages', 'rapatriement'],
                'nom_souscripteur' => 'Marie Dupont',
            ],
        ]);

        $results = compare($session);

        expect($results)->toHaveCount(10);
        expect((float) $results->first()->calculated_price)->toBe(11.50);  // ACS le moins cher
        expect((float) $results->last()->calculated_price)->toBe(24.90);   // Amaguiz le plus cher
        expect($results->first()->offer->company->name)->toBe('ACS Assurance');
        expect($results->last()->offer->company->name)->toBe('Amaguiz');

        // Vérification ordre strictement croissant
        $prices = $results->pluck('calculated_price')->map(fn ($p) => (float) $p)->values()->all();
        expect($prices)->toBe(collect($prices)->sort()->values()->all());
    });
});

// ─── Produit 2 — Assurance Auto Tous Risques ─────────────────────────────────
// Éligibilité : âge conducteur >= 18.

describe('Produit 2 — Assurance Auto Tous Risques (Direct Assurance, MAIF, AXA…)', function () {

    beforeEach(function () {
        $category = Category::factory()->create(['name' => 'Assurance & Protection', 'slug' => 'ap-auto']);
        $sector = Sector::factory()->for($category)->create(['name' => 'Assurance Automobile', 'slug' => 'sect-auto']);

        $this->product = Product::factory()->for($sector)->create([
            'name' => 'Assurance Auto Tous Risques',
            'slug' => 'assurance-auto-tous-risques',
            'is_active' => true,
        ]);

        $this->questionnaire = Questionnaire::factory()->for($this->product)->create([
            'name' => 'Questionnaire Assurance Auto',
            'is_active' => true,
        ]);

        // 12 questions — tous les types
        $this->qAge = q($this->questionnaire, 'age_conducteur', 'Âge du conducteur principal', QuestionInputType::Number, 1, step: 'conducteur');

        q($this->questionnaire, 'date_permis', 'Date d\'obtention du permis B', QuestionInputType::Date, 2, step: 'conducteur');

        q($this->questionnaire, 'marque_vehicule', 'Marque et modèle du véhicule', QuestionInputType::Text, 3, step: 'vehicule');

        q($this->questionnaire, 'usage_vehicule', 'Usage principal du véhicule', QuestionInputType::Radio, 4,
            ['prive' => 'Privé', 'pro' => 'Professionnel', 'mixte' => 'Mixte'], 'vehicule');

        q($this->questionnaire, 'annee_mise_circulation', 'Année de première mise en circulation', QuestionInputType::Number, 5, step: 'vehicule');

        q($this->questionnaire, 'bonus_malus', 'Coefficient bonus-malus actuel', QuestionInputType::Select, 6,
            ['0.50' => '0,50 (bonus maximum)', '0.60' => '0,60', '0.70' => '0,70', '0.80' => '0,80', '0.90' => '0,90', '1.00' => '1,00 (neutre)', '1.25' => '1,25 (malus)'], 'conducteur');

        q($this->questionnaire, 'sinistres_3ans', 'Avez-vous eu des sinistres dans les 3 dernières années ?', QuestionInputType::Boolean, 7, step: 'conducteur');

        q($this->questionnaire, 'detail_sinistres', 'Détail des sinistres déclarés', QuestionInputType::Textarea, 8, step: 'conducteur', required: false);

        q($this->questionnaire, 'kilometrage_annuel', 'Kilométrage annuel estimé', QuestionInputType::Select, 9,
            ['moins_5000' => 'Moins de 5 000 km', '5000_10000' => '5 000 – 10 000 km', '10000_20000' => '10 000 – 20 000 km', 'plus_20000' => 'Plus de 20 000 km'], 'vehicule');

        q($this->questionnaire, 'parking_couvert', 'Véhicule garé dans un parking couvert / box ?', QuestionInputType::Radio, 10,
            ['oui' => 'Oui, toujours', 'parfois' => 'Parfois', 'non' => 'Non, en voirie'], 'vehicule');

        q($this->questionnaire, 'options_souhaitees', 'Options de garantie souhaitées', QuestionInputType::Checkbox, 11,
            ['bris_glace' => 'Bris de glace', 'vol' => 'Vol / tentative de vol', 'incendie' => 'Incendie', 'conducteur' => 'Protection du conducteur'], 'couverture');

        q($this->questionnaire, 'nb_conducteurs_secondaires', 'Nombre de conducteurs secondaires', QuestionInputType::Number, 12, step: 'conducteur');

        // 10 compagnies — tarifs réels 25 ans, Renault Clio 2020, Paris, bonus 1.0
        $companies = [
            ['Direct Assurance', 'Direct Assurance Auto Confort', 720.00],
            ['Matmut', 'Matmut Auto Tous Risques', 780.00],
            ['GMF', 'GMF Auto Intégrale', 830.00],
            ['MAIF', 'MAIF Auto Tous Risques', 850.00],
            ['Covéa', 'MMA Auto Tous Risques', 960.00],
            ['AXA', 'AXA Auto Intégrale', 980.00],
            ['Groupama', 'Groupama Auto Confort', 920.00],
            ['Allianz', 'Allianz Auto Confort', 1050.00],
            ['LCL Assurances', 'LCL Auto Sérénité', 1120.00],
            ['Société Générale Assurances', 'SG Auto Confort', 1380.00],
        ];

        $this->offers = collect($companies)->map(fn ($c) => tap(
            mkOffer($category, $this->product, $c[0], $c[1], $c[2]),
            function (Offer $offer) {
                // Règle d'éligibilité sur chaque offre : âge conducteur >= 18
                OfferRule::factory()->for($offer)->for($this->qAge, 'question')->create([
                    'rule_type' => OfferRuleType::Eligibility->value,
                    'operator' => OfferRuleOperator::Gte->value,
                    'expected_value' => '18',
                    'is_active' => true,
                ]);
            }
        ));
    });

    it('retourne 10 offres pour un conducteur de 25 ans, classées par prime annuelle', function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => [
                'age_conducteur' => '25',
                'date_permis' => '2019-06-20',
                'marque_vehicule' => 'Renault Clio V 2020',
                'usage_vehicule' => 'prive',
                'annee_mise_circulation' => '2020',
                'bonus_malus' => '1.00',
                'sinistres_3ans' => 'non',
                'detail_sinistres' => '',
                'kilometrage_annuel' => '10000_20000',
                'parking_couvert' => 'non',
                'options_souhaitees' => ['bris_glace', 'conducteur'],
                'nb_conducteurs_secondaires' => '0',
            ],
        ]);

        $results = compare($session);

        expect($results)->toHaveCount(10);
        expect((float) $results->first()->calculated_price)->toBe(720.00);  // Direct Assurance
        expect((float) $results->last()->calculated_price)->toBe(1380.00); // SG
        expect($results->first()->offer->company->name)->toBe('Direct Assurance');

        $prices = $results->pluck('calculated_price')->map(fn ($p) => (float) $p)->values()->all();
        expect($prices)->toBe(collect($prices)->sort()->values()->all());
    });

    it('retourne 0 offres pour un conducteur de 16 ans (éligibilité age >= 18 non satisfaite)', function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => ['age_conducteur' => '16', 'bonus_malus' => '1.00', 'usage_vehicule' => 'prive'],
        ]);

        $results = compare($session);

        expect($results)->toHaveCount(0);
        expect(ComparisonResult::where('comparison_session_id', $session->id)
            ->where('is_eligible', false)->count())->toBe(10);
    });
});

// ─── Produit 3 — Mutuelle Santé Senior ───────────────────────────────────────
// Éligibilité : âge >= 55 (produit senior).

describe('Produit 3 — Mutuelle Santé Senior (Harmonie, MGEN, AG2R, SwissLife…)', function () {

    beforeEach(function () {
        $category = Category::factory()->create(['name' => 'Assurance & Protection', 'slug' => 'ap-sante']);
        $sector = Sector::factory()->for($category)->create(['name' => 'Mutuelle Santé', 'slug' => 'sect-sante']);

        $this->product = Product::factory()->for($sector)->create([
            'name' => 'Mutuelle Santé Senior',
            'slug' => 'mutuelle-sante-senior',
            'is_active' => true,
        ]);

        $this->questionnaire = Questionnaire::factory()->for($this->product)->create([
            'name' => 'Questionnaire Mutuelle Senior',
            'is_active' => true,
        ]);

        $this->qAge = q($this->questionnaire, 'age', 'Votre âge', QuestionInputType::Number, 1, step: 'profil');

        q($this->questionnaire, 'date_naissance', 'Date de naissance', QuestionInputType::Date, 2, step: 'profil');

        q($this->questionnaire, 'regime_secu', 'Régime de sécurité sociale', QuestionInputType::Select, 3,
            ['general' => 'Régime général', 'msa' => 'MSA (agricole)', 'rsi' => 'RSI / Indépendants', 'fnmf' => 'FNMF (fonctionnaires)'], 'profil');

        q($this->questionnaire, 'situation_familiale', 'Situation familiale', QuestionInputType::Radio, 4,
            ['celibataire' => 'Célibataire', 'marie' => 'Marié(e)', 'pacse' => 'Pacsé(e)', 'divorce' => 'Divorcé(e) / Séparé(e)'], 'profil');

        q($this->questionnaire, 'pathologie_chronique', 'Souffrez-vous d\'une pathologie chronique ?', QuestionInputType::Boolean, 5, step: 'sante');

        q($this->questionnaire, 'description_pathologies', 'Décrivez vos antécédents médicaux importants', QuestionInputType::Textarea, 6, step: 'sante', required: false);

        q($this->questionnaire, 'niveau_remboursement', 'Niveau de remboursement souhaité', QuestionInputType::Radio, 7,
            ['eco' => 'Économique (100% sécu)', 'confort' => 'Confort (150–200%)', 'premium' => 'Premium (250%+)'], 'couverture');

        q($this->questionnaire, 'besoins_specifiques', 'Postes prioritaires', QuestionInputType::Checkbox, 8,
            ['dentaire' => 'Soins dentaires', 'optique' => 'Optique / Lunettes', 'hospit' => 'Hospitalisation', 'med_douces' => 'Médecines douces'], 'couverture');

        q($this->questionnaire, 'date_souhaitee', 'Date de prise d\'effet souhaitée', QuestionInputType::Date, 9, step: 'contrat');

        q($this->questionnaire, 'medecin_traitant', 'Avez-vous déclaré un médecin traitant ?', QuestionInputType::Boolean, 10, step: 'sante');

        q($this->questionnaire, 'nom_complet', 'Nom et prénom', QuestionInputType::Text, 11, step: 'identite');

        // 10 compagnies — tarifs réels 65 ans, individuel, confort (€/mois)
        $companies = [
            ['MNT Mutuelle', 'MNT Senior Confort', 85.00],
            ['MGEN', 'MGEN Senior Confort', 88.00],
            ['Harmonie Mutuelle', 'Harmonie Senior Confort', 95.00],
            ['Groupama Santé', 'Groupama Senior Confort', 98.00],
            ['AG2R La Mondiale', 'AG2R Génération Santé Senior', 105.00],
            ['Malakoff Humanis', 'Malakoff Senior Sérénité', 112.00],
            ['SwissLife', 'SwissLife Santé Senior', 128.00],
            ['Allianz Santé', 'Allianz Senior Confort', 138.00],
            ['AXA Santé', 'AXA Senior Premium', 145.00],
            ['Generali', 'Generali Senior Protection', 162.00],
        ];

        $this->offers = collect($companies)->map(fn ($c) => tap(
            mkOffer($category, $this->product, $c[0], $c[1], $c[2]),
            function (Offer $offer) {
                OfferRule::factory()->for($offer)->for($this->qAge, 'question')->create([
                    'rule_type' => OfferRuleType::Eligibility->value,
                    'operator' => OfferRuleOperator::Gte->value,
                    'expected_value' => '55',
                    'is_active' => true,
                ]);
            }
        ));
    });

    it('retourne 10 mutuelles pour un senior de 65 ans, classées par cotisation mensuelle', function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => [
                'age' => '65',
                'date_naissance' => '1961-03-15',
                'regime_secu' => 'general',
                'situation_familiale' => 'marie',
                'pathologie_chronique' => 'non',
                'description_pathologies' => '',
                'niveau_remboursement' => 'confort',
                'besoins_specifiques' => ['dentaire', 'optique'],
                'date_souhaitee' => '2026-09-01',
                'medecin_traitant' => 'oui',
                'nom_complet' => 'Pierre Martin',
            ],
        ]);

        $results = compare($session);

        expect($results)->toHaveCount(10);
        expect((float) $results->first()->calculated_price)->toBe(85.00);   // MNT
        expect((float) $results->last()->calculated_price)->toBe(162.00);  // Generali
        expect($results->first()->offer->company->name)->toBe('MNT Mutuelle');
    });

    it("n'affiche aucune offre pour une personne de 45 ans (produit senior 55+)", function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => ['age' => '45', 'niveau_remboursement' => 'confort'],
        ]);

        expect(compare($session))->toHaveCount(0);
    });
});

// ─── Produit 4 — Assurance Habitation Locataire ──────────────────────────────

describe('Produit 4 — Assurance Habitation Locataire (Macif, MAIF, AXA, Direct Assurance…)', function () {

    beforeEach(function () {
        $category = Category::factory()->create(['name' => 'Assurance & Protection', 'slug' => 'ap-habitation']);
        $sector = Sector::factory()->for($category)->create(['name' => 'Assurance Habitation', 'slug' => 'sect-habitation']);

        $this->product = Product::factory()->for($sector)->create([
            'name' => 'Assurance Habitation Locataire',
            'slug' => 'assurance-habitation-locataire',
            'is_active' => true,
        ]);

        $this->questionnaire = Questionnaire::factory()->for($this->product)->create([
            'name' => 'Questionnaire Habitation Locataire',
            'is_active' => true,
        ]);

        q($this->questionnaire, 'superficie', 'Superficie du logement (m²)', QuestionInputType::Number, 1, step: 'logement');
        q($this->questionnaire, 'type_logement', 'Type de logement', QuestionInputType::Radio, 2,
            ['appartement' => 'Appartement', 'maison' => 'Maison individuelle'], 'logement');
        q($this->questionnaire, 'adresse_ville', 'Ville de résidence', QuestionInputType::Text, 3, step: 'logement');
        q($this->questionnaire, 'nb_pieces', 'Nombre de pièces principales', QuestionInputType::Select, 4,
            ['studio' => 'Studio / F1', 't2' => 'T2', 't3' => 'T3', 't4' => 'T4', 't5' => 'T5 et plus'], 'logement');
        q($this->questionnaire, 'date_entree', 'Date d\'entrée dans le logement', QuestionInputType::Date, 5, step: 'logement');
        q($this->questionnaire, 'valeur_mobilier', 'Valeur estimée du mobilier (€)', QuestionInputType::Number, 6, step: 'biens');
        q($this->questionnaire, 'alarme_installee', 'Le logement est-il équipé d\'une alarme ?', QuestionInputType::Boolean, 7, step: 'securite');
        q($this->questionnaire, 'presence_gardien', 'Y a-t-il un gardien ou une loge dans l\'immeuble ?', QuestionInputType::Radio, 8,
            ['oui' => 'Oui', 'non' => 'Non'], 'securite');
        q($this->questionnaire, 'objets_valeur', 'Décrivez les objets de valeur à assurer', QuestionInputType::Textarea, 9, step: 'biens', required: false);
        q($this->questionnaire, 'options_souhaitees', 'Garanties optionnelles souhaitées', QuestionInputType::Checkbox, 10,
            ['elec' => 'Dommages électriques', 'jardin' => 'Jardin / terrasse', 'cave' => 'Cave / grenier', 'piscine' => 'Piscine'], 'couverture');
        q($this->questionnaire, 'etage', 'À quel étage se situe le logement ?', QuestionInputType::Number, 11, step: 'logement');

        // Tarifs réels — T2 45m², Marseille, sans alarme (€/an)
        $companies = [
            ['Macif', 'Macif Habitation Locataire', 78.00],
            ['Direct Assurance', 'Direct Assurance Habitation', 85.00],
            ['Maaf', 'Maaf Habitation Locataire', 88.00],
            ['Matmut', 'Matmut Habitation Confort', 92.00],
            ['MAIF', 'MAIF Habitation Sereine', 98.00],
            ['GMF', 'GMF Habitation Confort', 102.00],
            ['MMA', 'MMA Habitation Locataire', 108.00],
            ['Groupama', 'Groupama Habitation Confort', 115.00],
            ['AXA', 'AXA Habitation Locataire Confort', 120.00],
            ['Allianz', 'Allianz Habitation Locataire', 135.00],
        ];

        $this->offers = collect($companies)->map(fn ($c) => mkOffer($category, $this->product, $c[0], $c[1], $c[2]));
    });

    it('retourne 10 offres habitation classées par prime annuelle', function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => [
                'superficie' => '45',
                'type_logement' => 'appartement',
                'adresse_ville' => 'Marseille',
                'nb_pieces' => 't2',
                'date_entree' => '2023-09-01',
                'valeur_mobilier' => '8000',
                'alarme_installee' => 'non',
                'presence_gardien' => 'non',
                'objets_valeur' => 'Ordinateur portable MacBook, vélo électrique',
                'options_souhaitees' => ['elec', 'cave'],
                'etage' => '3',
            ],
        ]);

        $results = compare($session);

        expect($results)->toHaveCount(10);
        expect((float) $results->first()->calculated_price)->toBe(78.00);   // Macif
        expect((float) $results->last()->calculated_price)->toBe(135.00);  // Allianz
        expect($results->first()->offer->company->name)->toBe('Macif');
    });
});

// ─── Produit 5 — Compte Bancaire Standard ────────────────────────────────────

describe('Produit 5 — Compte Bancaire Standard (Boursorama, Fortuneo, N26, BNP, SG…)', function () {

    beforeEach(function () {
        $category = Category::factory()->create(['name' => 'Finance & Banque', 'slug' => 'fb-banque']);
        $sector = Sector::factory()->for($category)->create(['name' => 'Banque en ligne', 'slug' => 'sect-banque']);

        $this->product = Product::factory()->for($sector)->create([
            'name' => 'Compte Courant Individuel',
            'slug' => 'compte-courant-individuel',
            'is_active' => true,
        ]);

        $this->questionnaire = Questionnaire::factory()->for($this->product)->create([
            'name' => 'Questionnaire Ouverture Compte',
            'is_active' => true,
        ]);

        q($this->questionnaire, 'revenus_mensuels', 'Revenus nets mensuels (€)', QuestionInputType::Number, 1, step: 'profil');
        q($this->questionnaire, 'situation_pro', 'Situation professionnelle', QuestionInputType::Select, 2,
            ['salarie' => 'Salarié CDI/CDD', 'independant' => 'Indépendant / TNS', 'etudiant' => 'Étudiant', 'retraite' => 'Retraité', 'sans_emploi' => 'Sans emploi'], 'profil');
        q($this->questionnaire, 'age', 'Âge', QuestionInputType::Number, 3, step: 'profil');
        q($this->questionnaire, 'type_carte', 'Type de carte bancaire souhaitée', QuestionInputType::Radio, 4,
            ['visa_classic' => 'Visa Classic', 'visa_premier' => 'Visa Premier', 'mc_gold' => 'Mastercard Gold'], 'services');
        q($this->questionnaire, 'gestion_preferee', 'Mode de gestion préféré', QuestionInputType::Radio, 5,
            ['ligne' => '100% en ligne', 'agence' => 'Avec conseiller en agence', 'mixte' => 'Mixte'], 'services');
        q($this->questionnaire, 'services_souhaites', 'Services complémentaires souhaités', QuestionInputType::Checkbox, 6,
            ['app_mobile' => 'Application mobile avancée', 'conseiller' => 'Conseiller dédié', 'epargne' => 'Livret d\'épargne associé', 'intl' => 'Virements internationaux SEPA'], 'services');
        q($this->questionnaire, 'ville', 'Ville de résidence', QuestionInputType::Text, 7, step: 'contact');
        q($this->questionnaire, 'decouvert_autorise', 'Souhaitez-vous un découvert autorisé ?', QuestionInputType::Boolean, 8, step: 'services');
        q($this->questionnaire, 'date_ouverture', 'Date d\'ouverture souhaitée', QuestionInputType::Date, 9, step: 'contrat');
        q($this->questionnaire, 'deja_client', 'Êtes-vous déjà client de cet établissement ?', QuestionInputType::Boolean, 10, step: 'profil');
        q($this->questionnaire, 'besoins_specifiques', 'Besoins particuliers à préciser', QuestionInputType::Textarea, 11, step: 'contact', required: false);

        // Frais mensuels réels (€/mois) — classement Que Choisir 2025
        $companies = [
            ['Boursorama Banque', 'Boursorama Ultim', 0.00],
            ['Fortuneo Banque', 'Fortuneo Fosfo', 2.90],
            ['Hello bank!', 'Hello bank! Basic', 4.90],
            ['N26', 'N26 Standard', 5.99],
            ['Orange Bank', 'Orange Bank Formule Standard', 7.99],
            ['LCL', 'LCL Essentiel', 9.00],
            ['Société Générale', 'SG Sobrio', 12.00],
            ['BNP Paribas', 'BNP Paribas Essentiel', 14.00],
            ['Crédit Agricole', 'CA Modulo Plus', 16.00],
            ['HSBC France', 'HSBC Everyday', 25.00],
        ];

        $this->offers = collect($companies)->map(fn ($c) => mkOffer($category, $this->product, $c[0], $c[1], $c[2]));
    });

    it('affiche les 10 comptes du moins cher au plus cher, Boursorama à 0€ en premier', function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => [
                'revenus_mensuels' => '2200',
                'situation_pro' => 'salarie',
                'age' => '30',
                'type_carte' => 'visa_classic',
                'gestion_preferee' => 'ligne',
                'services_souhaites' => ['app_mobile', 'epargne'],
                'ville' => 'Lyon',
                'decouvert_autorise' => 'oui',
                'date_ouverture' => '2026-08-01',
                'deja_client' => 'non',
                'besoins_specifiques' => '',
            ],
        ]);

        $results = compare($session);

        expect($results)->toHaveCount(10);
        expect((float) $results->first()->calculated_price)->toBe(0.00);   // Boursorama gratuit
        expect((float) $results->last()->calculated_price)->toBe(25.00);   // HSBC
        expect($results->first()->offer->company->name)->toBe('Boursorama Banque');
        expect($results->last()->offer->company->name)->toBe('HSBC France');
    });
});

// ─── Produit 6 — Prêt Personnel Consommation ─────────────────────────────────
// Éligibilité : revenu >= 1 200 €/mois.

describe('Produit 6 — Prêt Personnel (Younited, Cofidis, Cetelem, BNP, SG…)', function () {

    beforeEach(function () {
        $category = Category::factory()->create(['name' => 'Finance & Banque', 'slug' => 'fb-credit']);
        $sector = Sector::factory()->for($category)->create(['name' => 'Crédit Consommation', 'slug' => 'sect-credit']);

        $this->product = Product::factory()->for($sector)->create([
            'name' => 'Prêt Personnel Consommation',
            'slug' => 'pret-personnel-consommation',
            'is_active' => true,
        ]);

        $this->questionnaire = Questionnaire::factory()->for($this->product)->create([
            'name' => 'Questionnaire Prêt Personnel',
            'is_active' => true,
        ]);

        q($this->questionnaire, 'montant_emprunt', 'Montant souhaité (€)', QuestionInputType::Number, 1, step: 'projet');
        q($this->questionnaire, 'duree_mois', 'Durée de remboursement', QuestionInputType::Select, 2,
            ['m_12' => '12 mois', 'm_24' => '24 mois', 'm_36' => '36 mois', 'm_48' => '48 mois', 'm_60' => '60 mois', 'm_72' => '72 mois', 'm_84' => '84 mois'], 'projet');
        $this->qRevenu = q($this->questionnaire, 'revenu_mensuel_net', 'Revenus nets mensuels (€)', QuestionInputType::Number, 3, step: 'profil');
        q($this->questionnaire, 'situation_pro', 'Situation professionnelle', QuestionInputType::Radio, 4,
            ['cdi' => 'CDI', 'cdd' => 'CDD', 'independant' => 'Indépendant', 'fonctionnaire' => 'Fonctionnaire', 'retraite' => 'Retraité'], 'profil');
        q($this->questionnaire, 'credits_en_cours', 'Avez-vous d\'autres crédits en cours ?', QuestionInputType::Boolean, 5, step: 'profil');
        q($this->questionnaire, 'detail_credits', 'Détail des crédits existants (type, mensualité)', QuestionInputType::Textarea, 6, step: 'profil', required: false);
        q($this->questionnaire, 'objet_credit', 'Objet de l\'emprunt', QuestionInputType::Select, 7,
            ['auto' => 'Automobile', 'travaux' => 'Travaux / rénovation', 'voyage' => 'Voyage', 'equipement' => 'Équipement électroménager', 'autre' => 'Autre'], 'projet');
        q($this->questionnaire, 'date_souhaitee', 'Date de déblocage souhaitée', QuestionInputType::Date, 8, step: 'projet');
        q($this->questionnaire, 'proprietaire', 'Statut d\'occupation', QuestionInputType::Radio, 9,
            ['proprio' => 'Propriétaire', 'locataire' => 'Locataire', 'loge_gratuitement' => 'Logé à titre gratuit'], 'profil');
        q($this->questionnaire, 'incidents_paiement', 'Avez-vous eu des incidents de paiement dans les 5 ans ?', QuestionInputType::Boolean, 10, step: 'profil');
        q($this->questionnaire, 'nom_complet', 'Nom et prénom', QuestionInputType::Text, 11, step: 'identite');

        // Mensualités réelles pour €10 000 sur 36 mois (€/mois)
        $companies = [
            ['Boursorama Crédit', 'Boursorama Prêt Personnel', 271.00],
            ['Hello bank! Crédit', 'Hello bank! Prêt Perso', 278.00],
            ['Younited Credit', 'Younited Prêt Personnel', 285.00],
            ['Cofidis', 'Cofidis Prêt Personnel', 298.00],
            ['Cetelem', 'Cetelem Prêt Personnel', 305.00],
            ['Sofinco', 'Sofinco Prêt Personnel', 312.00],
            ['Crédit Agricole', 'CA Prêt Personnel', 320.00],
            ['LCL', 'LCL Prêt Personnel', 315.00],
            ['BNP Paribas', 'BNP Paribas Prêt Personnel', 330.00],
            ['Société Générale', 'SG Prêt Personnel', 345.00],
        ];

        $this->offers = collect($companies)->map(fn ($c) => tap(
            mkOffer($category, $this->product, $c[0], $c[1], $c[2]),
            function (Offer $offer) {
                OfferRule::factory()->for($offer)->for($this->qRevenu, 'question')->create([
                    'rule_type' => OfferRuleType::Eligibility->value,
                    'operator' => OfferRuleOperator::Gte->value,
                    'expected_value' => '1200',
                    'is_active' => true,
                ]);
            }
        ));
    });

    it('retourne 10 offres pour un revenu de 2 500€, classées par mensualité', function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => [
                'montant_emprunt' => '10000',
                'duree_mois' => '36',
                'revenu_mensuel_net' => '2500',
                'situation_pro' => 'cdi',
                'credits_en_cours' => 'non',
                'detail_credits' => '',
                'objet_credit' => 'auto',
                'date_souhaitee' => '2026-09-01',
                'proprietaire' => 'locataire',
                'incidents_paiement' => 'non',
                'nom_complet' => 'Karim Ben Salah',
            ],
        ]);

        $results = compare($session);

        expect($results)->toHaveCount(10);
        expect((float) $results->first()->calculated_price)->toBe(271.00);  // Boursorama
        expect((float) $results->last()->calculated_price)->toBe(345.00);   // SG
    });

    it('refuse le dossier si le revenu est inférieur à 1 200€', function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => ['revenu_mensuel_net' => '900', 'montant_emprunt' => '10000', 'duree_mois' => '36'],
        ]);

        expect(compare($session))->toHaveCount(0);
    });
});

// ─── Produit 7 — Assurance Vie Temporaire ────────────────────────────────────

describe('Produit 7 — Assurance Vie Temporaire (SwissLife, AXA, Allianz, AG2R…)', function () {

    beforeEach(function () {
        $category = Category::factory()->create(['name' => 'Finance & Banque', 'slug' => 'fb-vie']);
        $sector = Sector::factory()->for($category)->create(['name' => 'Assurance Vie', 'slug' => 'sect-vie']);

        $this->product = Product::factory()->for($sector)->create([
            'name' => 'Assurance Vie Temporaire Décès',
            'slug' => 'assurance-vie-temporaire-deces',
            'is_active' => true,
        ]);

        $this->questionnaire = Questionnaire::factory()->for($this->product)->create([
            'name' => 'Questionnaire Assurance Vie',
            'is_active' => true,
        ]);

        $this->qAge = q($this->questionnaire, 'age', 'Âge du souscripteur', QuestionInputType::Number, 1, step: 'profil');
        q($this->questionnaire, 'date_naissance', 'Date de naissance', QuestionInputType::Date, 2, step: 'profil');
        q($this->questionnaire, 'fumeur', 'Êtes-vous fumeur ou ex-fumeur récent (< 2 ans) ?', QuestionInputType::Radio, 3,
            ['non' => 'Non fumeur', 'ex_fumeur' => 'Ex-fumeur (sevrage > 2 ans)', 'fumeur' => 'Fumeur actuel'], 'sante');
        q($this->questionnaire, 'capital_souhaite', 'Capital décès souhaité', QuestionInputType::Select, 4,
            ['cs_50000' => '50 000 €', 'cs_100000' => '100 000 €', 'cs_200000' => '200 000 €', 'cs_300000' => '300 000 €', 'cs_500000' => '500 000 €'], 'couverture');
        q($this->questionnaire, 'duree_couverture', 'Durée de la couverture (années)', QuestionInputType::Number, 5, step: 'couverture');
        q($this->questionnaire, 'sport_risque', 'Pratiquez-vous un sport classé à risque ?', QuestionInputType::Boolean, 6, step: 'sante');
        q($this->questionnaire, 'detail_sports', 'Si oui, précisez le sport pratiqué', QuestionInputType::Textarea, 7, step: 'sante', required: false);
        q($this->questionnaire, 'beneficiaires', 'Bénéficiaires désignés (nom, lien de parenté)', QuestionInputType::Text, 8, step: 'contrat');
        q($this->questionnaire, 'option_invalidite', 'Souhaitez-vous une option Invalidité Totale et Définitive ?', QuestionInputType::Boolean, 9, step: 'options');
        q($this->questionnaire, 'periodicite_paiement', 'Périodicité de paiement de la prime', QuestionInputType::Radio, 10,
            ['mensuel' => 'Mensuelle', 'trimestriel' => 'Trimestrielle', 'annuel' => 'Annuelle'], 'contrat');
        q($this->questionnaire, 'antecedents_sante', 'Antécédents médicaux à signaler', QuestionInputType::Checkbox, 11,
            ['diabete' => 'Diabète', 'hypertension' => 'Hypertension', 'cardio' => 'Cardiopathie', 'cancer' => 'Cancer (traité)', 'aucun' => 'Aucun antécédent'], 'sante');

        // Primes réelles — 40 ans, non-fumeur, 200 000 €, 20 ans (€/mois)
        $companies = [
            ['LPA (La Préservation Assurance)', 'LPA Vie Temporaire 20 ans', 28.00],
            ['April Prévoyance', 'April Vie Temporaire Confort', 32.00],
            ['SwissLife', 'SwissLife Vie Temporaire', 35.00],
            ['Allianz', 'Allianz Vie Temporaire', 38.00],
            ['Groupama', 'Groupama Vie Temporaire', 40.00],
            ['Generali', 'Generali Vie Temporaire', 45.00],
            ['AXA', 'AXA Protection Décès', 42.00],
            ['AG2R La Mondiale', 'AG2R Décès Temporaire', 48.00],
            ['Cardif (BNP)', 'Cardif Vie Temporaire', 52.00],
            ['Malakoff Humanis', 'Malakoff Vie Temporaire', 55.00],
        ];

        $this->offers = collect($companies)->map(fn ($c) => tap(
            mkOffer($category, $this->product, $c[0], $c[1], $c[2]),
            function (Offer $offer) {
                OfferRule::factory()->for($offer)->for($this->qAge, 'question')->create([
                    'rule_type' => OfferRuleType::Eligibility->value,
                    'operator' => OfferRuleOperator::Lt->value,
                    'expected_value' => '75',
                    'is_active' => true,
                ]);
            }
        ));
    });

    it('retourne 10 offres pour un souscripteur de 40 ans, classées par prime mensuelle', function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => [
                'age' => '40',
                'date_naissance' => '1986-02-10',
                'fumeur' => 'non',
                'capital_souhaite' => '200000',
                'duree_couverture' => '20',
                'sport_risque' => 'non',
                'detail_sports' => '',
                'beneficiaires' => 'Sophie Martin, conjoint',
                'option_invalidite' => 'oui',
                'periodicite_paiement' => 'mensuel',
                'antecedents_sante' => ['aucun'],
            ],
        ]);

        $results = compare($session);

        expect($results)->toHaveCount(10);
        expect((float) $results->first()->calculated_price)->toBe(28.00);  // LPA
        expect((float) $results->last()->calculated_price)->toBe(55.00);   // Malakoff
        expect($results->first()->offer->company->name)->toBe('LPA (La Préservation Assurance)');
    });
});

// ─── Produit 8 — Forfait Mobile 5G 100 Go ────────────────────────────────────

describe('Produit 8 — Forfait Mobile 5G 100 Go (Free, SFR, Orange, Bouygues…)', function () {

    beforeEach(function () {
        $category = Category::factory()->create(['name' => 'Télécom & Tech', 'slug' => 'tt-mobile']);
        $sector = Sector::factory()->for($category)->create(['name' => 'Téléphonie Mobile', 'slug' => 'sect-mobile']);

        $this->product = Product::factory()->for($sector)->create([
            'name' => 'Forfait Mobile 5G 100 Go',
            'slug' => 'forfait-mobile-5g-100go',
            'is_active' => true,
        ]);

        $this->questionnaire = Questionnaire::factory()->for($this->product)->create([
            'name' => 'Questionnaire Forfait Mobile',
            'is_active' => true,
        ]);

        q($this->questionnaire, 'engagement', 'Type d\'engagement souhaité', QuestionInputType::Radio, 1,
            ['sans' => 'Sans engagement', '12mois' => '12 mois', '24mois' => '24 mois'], 'abonnement');
        q($this->questionnaire, 'data_consommation', 'Consommation data mensuelle estimée', QuestionInputType::Select, 2,
            ['20go' => '20 Go', '50go' => '50 Go', '100go' => '100 Go', '200go' => '200 Go', 'illimitee' => 'Illimitée'], 'abonnement');
        q($this->questionnaire, 'telephone_inclus', 'Souhaitez-vous un smartphone inclus ?', QuestionInputType::Boolean, 3, step: 'abonnement');
        q($this->questionnaire, 'marque_telephone', 'Marque de smartphone souhaitée (si inclus)', QuestionInputType::Text, 4, step: 'abonnement', required: false);
        q($this->questionnaire, 'budget_telephone', 'Budget pour le téléphone (€)', QuestionInputType::Number, 5, step: 'abonnement', required: false);
        q($this->questionnaire, 'utilisation_europe', 'Utilisez-vous votre forfait en Europe ?', QuestionInputType::Boolean, 6, step: 'usage');
        q($this->questionnaire, 'options_souhaitees', 'Options souhaitées', QuestionInputType::Checkbox, 7,
            ['sms_illi' => 'SMS/MMS illimités', 'appels_illi' => 'Appels illimités', 'roaming' => 'Roaming international', 'hotspot' => 'Partage de connexion'], 'options');
        q($this->questionnaire, 'operateur_actuel', 'Votre opérateur actuel', QuestionInputType::Select, 8,
            ['orange' => 'Orange', 'sfr' => 'SFR', 'bouygues' => 'Bouygues Telecom', 'free' => 'Free Mobile', 'autre' => 'Autre / MVNO'], 'profil');
        q($this->questionnaire, 'date_portabilite', 'Date de portabilité / résiliation souhaitée', QuestionInputType::Date, 9, step: 'contrat', required: false);
        q($this->questionnaire, 'exigences_couverture', 'Zones géographiques prioritaires pour la couverture', QuestionInputType::Textarea, 10, step: 'contact', required: false);
        q($this->questionnaire, 'conserver_numero', 'Souhaitez-vous conserver votre numéro actuel ?', QuestionInputType::Boolean, 11, step: 'abonnement');

        // Tarifs réels opérateurs français (€/mois, sans engagement, 100 Go 5G — janv. 2026)
        $companies = [
            ['Free Mobile', 'Free Forfait 5G 100 Go', 9.99],
            ['Cdiscount Mobile', 'Cdiscount Mobile 5G 100 Go', 11.99],
            ['Prixtel', 'Prixtel La Série 5G', 12.99],
            ['NRJ Mobile', 'NRJ Mobile Woot 5G', 14.99],
            ['RED by SFR', 'RED Forfait 5G 100 Go', 16.99],
            ['La Poste Mobile', 'La Poste Mobile 5G', 19.99],
            ['Bouygues Telecom', 'Bouygues Forfait 5G 100 Go', 21.99],
            ['SFR', 'SFR Power 5G 100 Go', 24.99],
            ['Orange', 'Orange Forfait 5G 100 Go', 29.99],
            ['Virgin Mobile', 'Virgin Mobile 5G Starter', 34.99],
        ];

        $this->offers = collect($companies)->map(fn ($c) => mkOffer($category, $this->product, $c[0], $c[1], $c[2]));
    });

    it('retourne 10 forfaits classés du moins cher (Free 9,99€) au plus cher (Virgin 34,99€)', function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => [
                'engagement' => 'sans',
                'data_consommation' => '100go',
                'telephone_inclus' => 'non',
                'marque_telephone' => '',
                'budget_telephone' => '0',
                'utilisation_europe' => 'oui',
                'options_souhaitees' => ['sms_illi', 'appels_illi', 'hotspot'],
                'operateur_actuel' => 'orange',
                'date_portabilite' => '2026-09-01',
                'exigences_couverture' => 'Paris, Lyon, Marseille — déplacements fréquents TGV',
                'conserver_numero' => 'oui',
            ],
        ]);

        $results = compare($session);

        expect($results)->toHaveCount(10);
        expect((float) $results->first()->calculated_price)->toBe(9.99);   // Free
        expect((float) $results->last()->calculated_price)->toBe(34.99);   // Virgin
        expect($results->first()->offer->company->name)->toBe('Free Mobile');

        $prices = $results->pluck('calculated_price')->map(fn ($p) => (float) $p)->values()->all();
        expect($prices)->toBe(collect($prices)->sort()->values()->all());
    });
});

// ─── Produit 9 — Assurance Animaux Chien et Chat ─────────────────────────────

describe('Produit 9 — Assurance Animaux (SantéVet, Agria, AXA Animaux, MAIF…)', function () {

    beforeEach(function () {
        $category = Category::factory()->create(['name' => 'Assurance & Protection', 'slug' => 'ap-animaux']);
        $sector = Sector::factory()->for($category)->create(['name' => 'Assurance Animaux de Compagnie', 'slug' => 'sect-animaux']);

        $this->product = Product::factory()->for($sector)->create([
            'name' => 'Assurance Santé Chien et Chat',
            'slug' => 'assurance-sante-chien-chat',
            'is_active' => true,
        ]);

        $this->questionnaire = Questionnaire::factory()->for($this->product)->create([
            'name' => 'Questionnaire Assurance Animaux',
            'is_active' => true,
        ]);

        q($this->questionnaire, 'espece', 'Espèce de l\'animal', QuestionInputType::Radio, 1,
            ['chien' => 'Chien', 'chat' => 'Chat'], 'animal');
        q($this->questionnaire, 'race', 'Race de l\'animal', QuestionInputType::Text, 2, step: 'animal');
        q($this->questionnaire, 'age_animal', 'Âge de l\'animal (années)', QuestionInputType::Number, 3, step: 'animal');
        q($this->questionnaire, 'date_naissance_animal', 'Date de naissance de l\'animal', QuestionInputType::Date, 4, step: 'animal');
        q($this->questionnaire, 'sterilise', 'L\'animal est-il stérilisé / castré ?', QuestionInputType::Boolean, 5, step: 'sante');
        q($this->questionnaire, 'vaccine', 'L\'animal est-il à jour de ses vaccinations ?', QuestionInputType::Boolean, 6, step: 'sante');
        q($this->questionnaire, 'identification', 'Mode d\'identification de l\'animal', QuestionInputType::Radio, 7,
            ['puce' => 'Puce électronique', 'tatouage' => 'Tatouage', 'non' => 'Non identifié'], 'sante');
        q($this->questionnaire, 'niveau_couverture', 'Niveau de couverture souhaité', QuestionInputType::Select, 8,
            ['basique' => 'Basique (accidents)', 'confort' => 'Confort (accidents + maladies)', 'premium' => 'Premium (complet)', 'excellence' => 'Excellence (tout risque)'], 'couverture');
        q($this->questionnaire, 'antecedents_medicaux', 'Antécédents médicaux ou pathologies connues', QuestionInputType::Textarea, 9, step: 'sante', required: false);
        q($this->questionnaire, 'prevention_souhaitee', 'Actes de prévention à rembourser', QuestionInputType::Checkbox, 10,
            ['vaccination' => 'Vaccination annuelle', 'vermifuges' => 'Vermifuges et antiparasitaires', 'bilan' => 'Bilan de santé annuel', 'sterilisation' => 'Stérilisation / castration'], 'options');
        q($this->questionnaire, 'poids_animal', 'Poids de l\'animal (kg)', QuestionInputType::Number, 11, step: 'animal');

        // Tarifs réels (€/mois) — Labrador 3 ans, mâle non stérilisé, niveau Confort
        $companies = [
            ['Assur O\'Poil', 'Assur O\'Poil Confort Chien', 14.90],
            ['MAIF Animaux', 'MAIF Compagnon Confort', 18.00],
            ['Bulle Bleue', 'Bulle Bleue Confort', 19.90],
            ['SantéVet', 'SantéVet Option 2', 22.50],
            ['MMA Animaux', 'MMA Compagnon Confort', 25.00],
            ['Agria', 'Agria Fidèle Compagnon', 28.00],
            ['Groupama Animaux', 'Groupama Compagnon Confort', 32.00],
            ['Swiss Life Animal', 'Swiss Life Animal Confort', 35.00],
            ['Allianz Animaux', 'Allianz Animaux Confort', 38.00],
            ['AXA Animaux', 'AXA Protection Animal', 42.00],
        ];

        $this->offers = collect($companies)->map(fn ($c) => mkOffer($category, $this->product, $c[0], $c[1], $c[2]));
    });

    it('retourne 10 offres classées par cotisation mensuelle — Assur O\'Poil en tête', function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => [
                'espece' => 'chien',
                'race' => 'Labrador Retriever',
                'age_animal' => '3',
                'date_naissance_animal' => '2023-04-12',
                'sterilise' => 'non',
                'vaccine' => 'oui',
                'identification' => 'puce',
                'niveau_couverture' => 'confort',
                'antecedents_medicaux' => 'Dysplasie légère de la hanche détectée en 2025, sans traitement en cours',
                'prevention_souhaitee' => ['vaccination', 'vermifuges', 'bilan'],
                'poids_animal' => '32',
            ],
        ]);

        $results = compare($session);

        expect($results)->toHaveCount(10);
        expect((float) $results->first()->calculated_price)->toBe(14.90);  // Assur O'Poil
        expect((float) $results->last()->calculated_price)->toBe(42.00);   // AXA
        expect($results->first()->offer->company->name)->toBe("Assur O'Poil");
    });
});

// ─── Produit 10 — Assurance Scolaire Enfant ──────────────────────────────────
// Éligibilité : âge enfant entre 3 et 18 ans.

describe('Produit 10 — Assurance Scolaire Enfant (GMF, MAIF, AXA, Groupama, Allianz…)', function () {

    beforeEach(function () {
        $category = Category::factory()->create(['name' => 'Assurance & Protection', 'slug' => 'ap-scolaire']);
        $sector = Sector::factory()->for($category)->create(['name' => 'Assurance Scolaire et Périscolaire', 'slug' => 'sect-scolaire']);

        $this->product = Product::factory()->for($sector)->create([
            'name' => 'Assurance Scolaire Enfant',
            'slug' => 'assurance-scolaire-enfant',
            'is_active' => true,
        ]);

        $this->questionnaire = Questionnaire::factory()->for($this->product)->create([
            'name' => 'Questionnaire Assurance Scolaire',
            'is_active' => true,
        ]);

        $this->qAge = q($this->questionnaire, 'age_enfant', 'Âge de l\'enfant', QuestionInputType::Number, 1, step: 'enfant');
        q($this->questionnaire, 'date_naissance_enfant', 'Date de naissance de l\'enfant', QuestionInputType::Date, 2, step: 'enfant');
        q($this->questionnaire, 'niveau_scolaire', 'Niveau scolaire actuel', QuestionInputType::Select, 3,
            ['maternelle' => 'Maternelle (3–5 ans)', 'cp_cm2' => 'École primaire (CP–CM2)', 'college' => 'Collège (6e–3e)', 'lycee' => 'Lycée (2nde–Terminale)'], 'scolarite');
        q($this->questionnaire, 'etablissement', 'Nom et ville de l\'établissement scolaire', QuestionInputType::Text, 4, step: 'scolarite');
        q($this->questionnaire, 'pratique_sport', 'L\'enfant pratique-t-il un sport en club ?', QuestionInputType::Boolean, 5, step: 'activites');
        q($this->questionnaire, 'sports_pratiques', 'Sports pratiqués en compétition', QuestionInputType::Checkbox, 6,
            ['football' => 'Football', 'natation' => 'Natation', 'judo' => 'Judo / Arts martiaux', 'athletisme' => 'Athlétisme', 'gym' => 'Gymnastique', 'autre' => 'Autre'], 'activites');
        q($this->questionnaire, 'besoins_medicaux', 'L\'enfant a-t-il des besoins médicaux particuliers ?', QuestionInputType::Boolean, 7, step: 'sante');
        q($this->questionnaire, 'description_sante', 'Description des besoins médicaux (PAI, allergie, handicap…)', QuestionInputType::Textarea, 8, step: 'sante', required: false);
        q($this->questionnaire, 'type_couverture', 'Type de couverture souhaitée', QuestionInputType::Radio, 9,
            ['basique' => 'Basique (RC + accidents scolaires)', 'etendue' => 'Étendue (+ activités périscolaires)', 'premium' => 'Premium (toutes activités + assistance)'], 'couverture');
        q($this->questionnaire, 'extension_extrascolaire', 'Souhaitez-vous une extension périscolaire et de vacances ?', QuestionInputType::Boolean, 10, step: 'options');
        q($this->questionnaire, 'nom_enfant', 'Nom et prénom de l\'enfant', QuestionInputType::Text, 11, step: 'identite');

        // Tarifs réels (€/an) — enfant 8 ans, CM1, avec sport en club
        $companies = [
            ['SMAF', 'SMAF Scolaire Confort', 18.00],
            ['Maaf', 'Maaf Scolaire Enfant', 19.00],
            ['GMF', 'GMF Scolaire Enfant Confort', 22.00],
            ['Matmut', 'Matmut Scolaire Confort', 24.00],
            ['MACIF', 'MACIF Scolaire', 26.00],
            ['MAIF', 'MAIF Scolaire Sérénis', 28.00],
            ['Groupama', 'Groupama Scolaire Confort', 30.00],
            ['AXA', 'AXA Scolaire Enfant', 35.00],
            ['MMA', 'MMA Scolaire Confort', 38.00],
            ['Allianz', 'Allianz Scolaire Premium', 42.00],
        ];

        $this->offers = collect($companies)->map(fn ($c) => tap(
            mkOffer($category, $this->product, $c[0], $c[1], $c[2]),
            function (Offer $offer) {
                // Borne inférieure : enfant >= 3 ans
                OfferRule::factory()->for($offer)->for($this->qAge, 'question')->create([
                    'rule_type' => OfferRuleType::Eligibility->value,
                    'operator' => OfferRuleOperator::Gte->value,
                    'expected_value' => '3',
                    'is_active' => true,
                ]);
                // Borne supérieure : enfant <= 18 ans
                OfferRule::factory()->for($offer)->for($this->qAge, 'question')->create([
                    'rule_type' => OfferRuleType::Eligibility->value,
                    'operator' => OfferRuleOperator::Lte->value,
                    'expected_value' => '18',
                    'is_active' => true,
                ]);
            }
        ));
    });

    it('retourne 10 offres pour un enfant de 8 ans, classées par cotisation annuelle', function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => [
                'age_enfant' => '8',
                'date_naissance_enfant' => '2018-03-22',
                'niveau_scolaire' => 'cp_cm2',
                'etablissement' => 'École primaire Jules Ferry, Bordeaux',
                'pratique_sport' => 'oui',
                'sports_pratiques' => ['football', 'natation'],
                'besoins_medicaux' => 'non',
                'description_sante' => '',
                'type_couverture' => 'etendue',
                'extension_extrascolaire' => 'oui',
                'nom_enfant' => 'Lucas Moreau',
            ],
        ]);

        $results = compare($session);

        expect($results)->toHaveCount(10);
        expect((float) $results->first()->calculated_price)->toBe(18.00);  // SMAF
        expect((float) $results->last()->calculated_price)->toBe(42.00);   // Allianz
        expect($results->first()->offer->company->name)->toBe('SMAF');
        expect($results->last()->offer->company->name)->toBe('Allianz');

        $prices = $results->pluck('calculated_price')->map(fn ($p) => (float) $p)->values()->all();
        expect($prices)->toBe(collect($prices)->sort()->values()->all());
    });

    it("n'affiche aucune offre pour un enfant de 20 ans (hors tranche scolaire)", function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => ['age_enfant' => '20', 'type_couverture' => 'basique'],
        ]);

        expect(compare($session))->toHaveCount(0);
    });

    it("n'affiche aucune offre pour un enfant de 2 ans (trop jeune)", function () {
        $session = ComparisonSession::factory()->create([
            'product_id' => $this->product->id,
            'questionnaire_id' => $this->questionnaire->id,
            'answers_json' => ['age_enfant' => '2', 'type_couverture' => 'basique'],
        ]);

        expect(compare($session))->toHaveCount(0);
    });
});
