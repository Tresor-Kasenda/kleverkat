<?php

namespace Database\Factories;

use App\Enums\ProductBillingFrequency;
use App\Enums\ProductPriceType;
use App\Models\Product;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $priceType = fake()->randomElement(ProductPriceType::cases());

        return [
            'sector_id' => Sector::factory(),
            'code' => strtoupper(Str::random(3)).'-'.fake()->numerify('###'),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'price_type' => $priceType,
            'base_price' => $priceType === ProductPriceType::Fixed ? fake()->randomFloat(2, 5, 500) : null,
            'currency' => fake()->randomElement(['USD', 'CDF', 'EUR']),
            'billing_frequency' => fake()->randomElement([...ProductBillingFrequency::cases(), null]),
            'min_age' => fake()->boolean(60) ? fake()->numberBetween(18, 30) : null,
            'max_age' => fake()->boolean(60) ? fake()->numberBetween(55, 75) : null,
            'min_insured_amount' => fake()->boolean(50) ? fake()->randomFloat(2, 100, 5000) : null,
            'max_insured_amount' => fake()->boolean(50) ? fake()->randomFloat(2, 10000, 500000) : null,
            'duration_months' => fake()->boolean(70) ? fake()->randomElement([12, 24, 36, 60]) : null,
            'waiting_period_days' => fake()->boolean(40) ? fake()->randomElement([30, 60, 90]) : null,
            'features' => [],
            'exclusions' => [],
            'sort_order' => fake()->numberBetween(0, 50),
            'is_active' => true,
            'is_featured' => false,
            'available_from' => null,
            'available_until' => null,
        ];
    }
}
