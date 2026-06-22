<?php

namespace Database\Factories;

use App\Enums\OfferRuleOperator;
use App\Enums\OfferRuleType;
use App\Models\Offer;
use App\Models\OfferRule;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfferRule>
 */
class OfferRuleFactory extends Factory
{
    /**
     * @return array<model-property<OfferRule>, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(OfferRuleType::cases());

        return [
            'offer_id' => Offer::factory(),
            'question_id' => Question::factory(),
            'rule_type' => $type,
            'operator' => fake()->randomElement(OfferRuleOperator::cases()),
            'expected_value' => fake()->word(),
            'weight' => $type === OfferRuleType::Scoring ? fake()->randomFloat(3, 0.5, 2.0) : null,
            'score_delta' => $type === OfferRuleType::Scoring ? fake()->randomFloat(2, -20, 30) : null,
            'price_delta' => $type === OfferRuleType::Pricing ? fake()->randomFloat(2, -50, 100) : null,
            'price_multiplier' => $type === OfferRuleType::Pricing ? fake()->randomFloat(4, 0.8, 1.5) : null,
            'priority' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }

    public function eligibility(): static
    {
        return $this->state([
            'rule_type' => OfferRuleType::Eligibility,
            'weight' => null,
            'score_delta' => null,
            'price_delta' => null,
            'price_multiplier' => null,
        ]);
    }

    public function scoring(): static
    {
        return $this->state([
            'rule_type' => OfferRuleType::Scoring,
            'weight' => 1.0,
            'score_delta' => 10.0,
            'price_delta' => null,
            'price_multiplier' => null,
        ]);
    }

    public function pricing(): static
    {
        return $this->state([
            'rule_type' => OfferRuleType::Pricing,
            'weight' => null,
            'score_delta' => null,
            'price_delta' => 0.0,
            'price_multiplier' => 1.15,
        ]);
    }
}
