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
        $name = $this->faker->unique()->words(3, true);

        return [
            'company_id' => Company::factory(),
            'product_id' => Product::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'short_description' => $this->faker->sentence(),
            'long_description' => $this->faker->paragraph(),
            'base_price' => $this->faker->randomFloat(2, 5, 500),
            'price_note' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'sort_order' => $this->faker->numberBetween(0, 50),
            'is_active' => true,
            'is_featured' => false,
        ];
    }
}
