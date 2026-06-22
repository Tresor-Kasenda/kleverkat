<?php

namespace Database\Factories;

use App\Models\Offer;
use App\Models\OfferFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfferFeature>
 */
class OfferFeatureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'offer_id' => Offer::factory(),
            'label' => fake()->word(),
            'value' => fake()->boolean(60) ? fake()->sentence() : null,
            'is_highlight' => fake()->boolean(20),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
