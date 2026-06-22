<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'company_id' => Company::factory(),
            'product_id' => Product::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(),
            'long_description' => fake()->paragraph(),
            'base_price' => fake()->randomFloat(2, 5, 500),
            'price_note' => fake()->boolean(30) ? fake()->sentence() : null,
            'sort_order' => fake()->numberBetween(0, 50),
            'is_active' => true,
            'is_featured' => false,
        ];
    }
}
