<?php

use App\Enums\OfferRuleOperator;
use App\Enums\OfferRuleType;
use App\Models\ComparisonSession;
use App\Models\Offer;
use App\Models\OfferRule;
use App\Models\Product;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Services\Comparison\ComparisonService;
use App\Services\Comparison\EligibilityEvaluator;
use App\Services\Comparison\PricingEvaluator;
use App\Services\Comparison\RuleMatcher;
use App\Services\Comparison\ScoringEvaluator;

// ─── RuleMatcher ─────────────────────────────────────────────────────────────

describe('RuleMatcher', function () {
    beforeEach(function () {
        $this->matcher = new RuleMatcher;
    });

    it('matches eq operator', function () {
        $rule = buildRule(OfferRuleOperator::Eq, 'oui');
        expect($this->matcher->matches($rule, ['usage' => 'oui']))->toBeTrue();
        expect($this->matcher->matches($rule, ['usage' => 'non']))->toBeFalse();
    });

    it('matches neq operator', function () {
        $rule = buildRule(OfferRuleOperator::Neq, 'pro');
        expect($this->matcher->matches($rule, ['usage' => 'prive']))->toBeTrue();
        expect($this->matcher->matches($rule, ['usage' => 'pro']))->toBeFalse();
    });

    it('matches numeric lt operator', function () {
        $rule = buildRule(OfferRuleOperator::Lt, '25');
        expect($this->matcher->matches($rule, ['age' => '20']))->toBeTrue();
        expect($this->matcher->matches($rule, ['age' => '25']))->toBeFalse();
        expect($this->matcher->matches($rule, ['age' => '30']))->toBeFalse();
    });

    it('matches numeric gte operator', function () {
        $rule = buildRule(OfferRuleOperator::Gte, '18');
        expect($this->matcher->matches($rule, ['age' => '18']))->toBeTrue();
        expect($this->matcher->matches($rule, ['age' => '17']))->toBeFalse();
    });

    it('matches in operator with comma-separated list', function () {
        $rule = buildRule(OfferRuleOperator::In, 'paris,lyon,marseille');
        expect($this->matcher->matches($rule, ['city' => 'paris']))->toBeTrue();
        expect($this->matcher->matches($rule, ['city' => 'bordeaux']))->toBeFalse();
    });

    it('matches not_in operator', function () {
        $rule = buildRule(OfferRuleOperator::NotIn, 'paris,lyon');
        expect($this->matcher->matches($rule, ['city' => 'bordeaux']))->toBeTrue();
        expect($this->matcher->matches($rule, ['city' => 'paris']))->toBeFalse();
    });

    it('returns false for numeric operator when answer is missing', function () {
        $rule = buildRule(OfferRuleOperator::Gte, '18');
        expect($this->matcher->matches($rule, []))->toBeFalse();
    });
});

// ─── EligibilityEvaluator ────────────────────────────────────────────────────

describe('EligibilityEvaluator', function () {
    beforeEach(function () {
        $this->evaluator = new EligibilityEvaluator(new RuleMatcher);
    });

    it('is eligible when no eligibility rules exist', function () {
        $offer = Offer::factory()->create();

        $result = $this->evaluator->evaluate($offer->load('rules'), []);

        expect($result['eligible'])->toBeTrue()
            ->and($result['checks'])->toBeEmpty();
    });

    it('is eligible when all rules are satisfied', function () {
        $setup = setupOfferWithQuestion();
        OfferRule::factory()->for($setup['offer'])->for($setup['question'])
            ->create(['rule_type' => OfferRuleType::Eligibility, 'operator' => OfferRuleOperator::Gte, 'expected_value' => '18', 'is_active' => true]);

        $offer = $setup['offer']->load('rules.question');
        $result = $this->evaluator->evaluate($offer, ['driver_age' => '25']);

        expect($result['eligible'])->toBeTrue();
    });

    it('is ineligible when a rule is not satisfied', function () {
        $setup = setupOfferWithQuestion();
        OfferRule::factory()->for($setup['offer'])->for($setup['question'])
            ->create(['rule_type' => OfferRuleType::Eligibility, 'operator' => OfferRuleOperator::Gte, 'expected_value' => '18', 'is_active' => true]);

        $offer = $setup['offer']->load('rules.question');
        $result = $this->evaluator->evaluate($offer, ['driver_age' => '16']);

        expect($result['eligible'])->toBeFalse()
            ->and($result['checks'])->toHaveCount(1)
            ->and($result['checks'][0]['matched'])->toBeFalse();
    });

    it('ignores inactive eligibility rules', function () {
        $setup = setupOfferWithQuestion();
        OfferRule::factory()->for($setup['offer'])->for($setup['question'])
            ->create(['rule_type' => OfferRuleType::Eligibility, 'operator' => OfferRuleOperator::Gte, 'expected_value' => '18', 'is_active' => false]);

        $offer = $setup['offer']->load('rules.question');
        $result = $this->evaluator->evaluate($offer, ['driver_age' => '15']);

        expect($result['eligible'])->toBeTrue();
    });
});

// ─── ScoringEvaluator ────────────────────────────────────────────────────────

describe('ScoringEvaluator', function () {
    beforeEach(function () {
        $this->evaluator = new ScoringEvaluator(new RuleMatcher);
    });

    it('returns zero score when no scoring rules exist', function () {
        $offer = Offer::factory()->create();
        $result = $this->evaluator->evaluate($offer->load('rules'), []);

        expect($result['score'])->toBe(0.0);
    });

    it('adds score_delta when rule matches', function () {
        $setup = setupOfferWithQuestion();
        OfferRule::factory()->for($setup['offer'])->for($setup['question'])
            ->create(['rule_type' => OfferRuleType::Scoring, 'operator' => OfferRuleOperator::Eq,
                'expected_value' => 'pro', 'score_delta' => 15.0, 'weight' => 1.0, 'is_active' => true]);

        $offer = $setup['offer']->load('rules.question');
        $result = $this->evaluator->evaluate($offer, ['driver_age' => 'pro']);

        expect($result['score'])->toBe(15.0);
    });

    it('applies weight to score_delta', function () {
        $setup = setupOfferWithQuestion();
        OfferRule::factory()->for($setup['offer'])->for($setup['question'])
            ->create(['rule_type' => OfferRuleType::Scoring, 'operator' => OfferRuleOperator::Eq,
                'expected_value' => 'oui', 'score_delta' => 10.0, 'weight' => 1.5, 'is_active' => true]);

        $offer = $setup['offer']->load('rules.question');
        $result = $this->evaluator->evaluate($offer, ['driver_age' => 'oui']);

        expect($result['score'])->toBe(15.0);
    });

    it('accumulates score from multiple matching rules', function () {
        $setup = setupOfferWithQuestion();
        $q2 = Question::factory()->for($setup['questionnaire'])->number()->create(['field_key' => 'experience']);

        OfferRule::factory()->for($setup['offer'])->for($setup['question'])
            ->create(['rule_type' => OfferRuleType::Scoring, 'operator' => OfferRuleOperator::Eq,
                'expected_value' => 'oui', 'score_delta' => 10.0, 'weight' => 1.0, 'is_active' => true]);
        OfferRule::factory()->for($setup['offer'])->for($q2)
            ->create(['rule_type' => OfferRuleType::Scoring, 'operator' => OfferRuleOperator::Gte,
                'expected_value' => '5', 'score_delta' => 20.0, 'weight' => 1.0, 'is_active' => true]);

        $offer = $setup['offer']->load('rules.question');
        $result = $this->evaluator->evaluate($offer, ['driver_age' => 'oui', 'experience' => '7']);

        expect($result['score'])->toBe(30.0);
    });
});

// ─── PricingEvaluator ────────────────────────────────────────────────────────

describe('PricingEvaluator', function () {
    beforeEach(function () {
        $this->evaluator = new PricingEvaluator(new RuleMatcher);
    });

    it('returns null price when offer has no base price', function () {
        $offer = Offer::factory()->create(['base_price' => null]);
        $result = $this->evaluator->evaluate($offer->load('rules'), []);

        expect($result['price'])->toBeNull();
    });

    it('returns base price unchanged when no pricing rules match', function () {
        $offer = Offer::factory()->create(['base_price' => 100.0]);
        $result = $this->evaluator->evaluate($offer->load('rules'), []);

        expect($result['price'])->toBe(100.0);
    });

    it('applies price_delta when rule matches', function () {
        $setup = setupOfferWithQuestion(basePrice: 100.0);
        OfferRule::factory()->for($setup['offer'])->for($setup['question'])
            ->create(['rule_type' => OfferRuleType::Pricing, 'operator' => OfferRuleOperator::Eq,
                'expected_value' => 'paris', 'price_delta' => 20.0, 'price_multiplier' => null, 'is_active' => true]);

        $offer = $setup['offer']->load('rules.question');
        $result = $this->evaluator->evaluate($offer, ['driver_age' => 'paris']);

        expect($result['price'])->toBe(120.0);
    });

    it('applies price_multiplier when rule matches', function () {
        $setup = setupOfferWithQuestion(basePrice: 100.0);
        OfferRule::factory()->for($setup['offer'])->for($setup['question'])
            ->create(['rule_type' => OfferRuleType::Pricing, 'operator' => OfferRuleOperator::Eq,
                'expected_value' => 'paris', 'price_delta' => null, 'price_multiplier' => 1.15, 'is_active' => true]);

        $offer = $setup['offer']->load('rules.question');
        $result = $this->evaluator->evaluate($offer, ['driver_age' => 'paris']);

        expect($result['price'])->toBe(115.0);
    });
});

// ─── ComparisonService (intégration) ─────────────────────────────────────────

describe('ComparisonService', function () {
    beforeEach(function () {
        $this->service = app(ComparisonService::class);
    });

    it('creates comparison results for all active offers', function () {
        $product = Product::factory()->create();
        $questionnaire = Questionnaire::factory()->state(['product_id' => $product->id])->create();
        Offer::factory()->count(3)->state(['product_id' => $product->id, 'is_active' => true])->create();

        $session = ComparisonSession::factory()->create([
            'product_id' => $product->id,
            'questionnaire_id' => $questionnaire->id,
            'answers_json' => [],
        ]);

        $this->service->compare($session);

        expect($session->results()->count())->toBe(3);
    });

    it('excludes inactive offers from results', function () {
        $product = Product::factory()->create();
        $questionnaire = Questionnaire::factory()->state(['product_id' => $product->id])->create();
        Offer::factory()->state(['product_id' => $product->id, 'is_active' => true])->create();
        Offer::factory()->state(['product_id' => $product->id, 'is_active' => false])->create();

        $session = ComparisonSession::factory()->create([
            'product_id' => $product->id,
            'questionnaire_id' => $questionnaire->id,
            'answers_json' => [],
        ]);

        $this->service->compare($session);

        expect($session->results()->count())->toBe(1);
    });

    it('marks session as completed after compare', function () {
        $product = Product::factory()->create();
        $questionnaire = Questionnaire::factory()->state(['product_id' => $product->id])->create();

        $session = ComparisonSession::factory()->create([
            'product_id' => $product->id,
            'questionnaire_id' => $questionnaire->id,
            'answers_json' => [],
        ]);

        expect($session->completed_at)->toBeNull();

        $this->service->compare($session);

        expect($session->fresh()->completed_at)->not->toBeNull();
    });

    it('marks ineligible when eligibility rule fails', function () {
        $setup = setupOfferWithQuestion();
        OfferRule::factory()->for($setup['offer'])->for($setup['question'])
            ->create(['rule_type' => OfferRuleType::Eligibility, 'operator' => OfferRuleOperator::Gte,
                'expected_value' => '18', 'is_active' => true]);

        $session = ComparisonSession::factory()->create([
            'product_id' => $setup['product']->id,
            'questionnaire_id' => $setup['questionnaire']->id,
            'answers_json' => ['driver_age' => '16'],
        ]);

        $this->service->compare($session);

        $result = $session->results()->first();
        expect($result->is_eligible)->toBeFalse()
            ->and($result->rank_position)->toBeNull();
    });

    it('ranks eligible offers by score descending', function () {
        $product = Product::factory()->create();
        $questionnaire = Questionnaire::factory()->state(['product_id' => $product->id])->create();
        $question = Question::factory()->for($questionnaire)->number()->create(['field_key' => 'age']);

        $offerA = Offer::factory()->state(['product_id' => $product->id, 'is_active' => true])->create();
        $offerB = Offer::factory()->state(['product_id' => $product->id, 'is_active' => true])->create();

        // Offer A gets +30 points when age >= 30
        OfferRule::factory()->for($offerA)->for($question)
            ->create(['rule_type' => OfferRuleType::Scoring, 'operator' => OfferRuleOperator::Gte,
                'expected_value' => '30', 'score_delta' => 30.0, 'weight' => 1.0, 'is_active' => true]);
        // Offer B gets +10 points when age >= 18
        OfferRule::factory()->for($offerB)->for($question)
            ->create(['rule_type' => OfferRuleType::Scoring, 'operator' => OfferRuleOperator::Gte,
                'expected_value' => '18', 'score_delta' => 10.0, 'weight' => 1.0, 'is_active' => true]);

        $session = ComparisonSession::factory()->create([
            'product_id' => $product->id,
            'questionnaire_id' => $questionnaire->id,
            'answers_json' => ['age' => '35'],
        ]);

        $this->service->compare($session);

        $ranked = $this->service->rankedResults($session);
        expect($ranked)->toHaveCount(2)
            ->and($ranked->first()->offer_id)->toBe($offerA->id)
            ->and($ranked->first()->rank_position)->toBe(1)
            ->and($ranked->last()->rank_position)->toBe(2);
    });

    it('stores explanation json with eligibility checks', function () {
        $setup = setupOfferWithQuestion();
        OfferRule::factory()->for($setup['offer'])->for($setup['question'])
            ->create(['rule_type' => OfferRuleType::Eligibility, 'operator' => OfferRuleOperator::Gte,
                'expected_value' => '18', 'is_active' => true]);

        $session = ComparisonSession::factory()->create([
            'product_id' => $setup['product']->id,
            'questionnaire_id' => $setup['questionnaire']->id,
            'answers_json' => ['driver_age' => '25'],
        ]);

        $this->service->compare($session);

        $result = $session->results()->first();
        expect($result->explanation_json)->toHaveKey('eligibility')
            ->and($result->explanation_json['eligibility'])->toHaveCount(1)
            ->and($result->explanation_json['eligibility'][0]['matched'])->toBeTrue();
    });
});

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * @return array{product: Product, questionnaire: Questionnaire, offer: Offer, question: Question}
 */
function setupOfferWithQuestion(float $basePrice = 0.0): array
{
    $product = Product::factory()->create();
    $questionnaire = Questionnaire::factory()->state(['product_id' => $product->id])->create();
    $offer = Offer::factory()->state(['product_id' => $product->id, 'is_active' => true, 'base_price' => $basePrice ?: null])->create();
    $question = Question::factory()->for($questionnaire)->number()->create(['field_key' => 'driver_age']);

    return compact('product', 'questionnaire', 'offer', 'question');
}

/**
 * Build a fake OfferRule stub for RuleMatcher tests (without persisting to DB).
 */
function buildRule(OfferRuleOperator $operator, string $expected): OfferRule
{
    $question = Question::factory()->create(['field_key' => match ($operator) {
        OfferRuleOperator::In, OfferRuleOperator::NotIn => 'city',
        OfferRuleOperator::Lt, OfferRuleOperator::Lte,
        OfferRuleOperator::Gt, OfferRuleOperator::Gte => 'age',
        default => 'usage',
    }]);

    return OfferRule::factory()->make([
        'operator' => $operator,
        'expected_value' => $expected,
        'rule_type' => OfferRuleType::Eligibility,
        'is_active' => true,
        'question_id' => $question->id,
    ])->setRelation('question', $question);
}
