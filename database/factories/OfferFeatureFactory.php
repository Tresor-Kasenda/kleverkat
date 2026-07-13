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
            'label' => $this->faker->word(),
            'value' => $this->faker->boolean(60) ? $this->faker->sentence() : null,
            'is_highlight' => $this->faker->boolean(20),
            'sort_order' => $this->faker->numberBetween(0, 20),
        ];
    }
}
