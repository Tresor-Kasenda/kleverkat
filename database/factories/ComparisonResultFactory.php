<?php

namespace Database\Factories;

use App\Models\ComparisonResult;
use App\Models\ComparisonSession;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComparisonResult>
 */
class ComparisonResultFactory extends Factory
{
    /**
     * @return array<model-property<ComparisonResult>, mixed>
     */
    public function definition(): array
    {
        $offer = Offer::factory()->create();

        return [
            'comparison_session_id' => ComparisonSession::factory(),
            'offer_id' => $offer->id,
            'company_id' => $offer->company_id,
            'is_eligible' => true,
            'score' => fake()->randomFloat(2, 0, 100),
            'calculated_price' => fake()->randomFloat(2, 10, 500),
            'explanation_json' => ['eligibility' => [], 'scoring' => [], 'pricing' => []],
            'rank_position' => 1,
        ];
    }

    public function ineligible(): static
    {
        return $this->state([
            'is_eligible' => false,
            'score' => null,
            'calculated_price' => null,
            'rank_position' => null,
        ]);
    }
}
