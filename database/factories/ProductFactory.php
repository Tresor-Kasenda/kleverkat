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
        $name = $this->faker->unique()->words(3, true);
        $priceType = $this->faker->randomElement(ProductPriceType::cases());

        return [
            'sector_id' => Sector::factory(),
            'code' => strtoupper(Str::random(3)).'-'.$this->faker->numerify('###'),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'short_description' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'price_type' => $priceType,
            'base_price' => $priceType === ProductPriceType::Fixed ? $this->faker->randomFloat(2, 5, 500) : null,
            'currency' => $this->faker->randomElement(['USD', 'CDF', 'EUR']),
            'billing_frequency' => $this->faker->randomElement([...ProductBillingFrequency::cases(), null]),
            'min_age' => $this->faker->boolean(60) ? $this->faker->numberBetween(18, 30) : null,
            'max_age' => $this->faker->boolean(60) ? $this->faker->numberBetween(55, 75) : null,
            'min_insured_amount' => $this->faker->boolean(50) ? $this->faker->randomFloat(2, 100, 5000) : null,
            'max_insured_amount' => $this->faker->boolean(50) ? $this->faker->randomFloat(2, 10000, 500000) : null,
            'duration_months' => $this->faker->boolean(70) ? $this->faker->randomElement([12, 24, 36, 60]) : null,
            'waiting_period_days' => $this->faker->boolean(40) ? $this->faker->randomElement([30, 60, 90]) : null,
            'features' => [],
            'exclusions' => [],
            'sort_order' => $this->faker->numberBetween(0, 50),
            'is_active' => true,
            'is_featured' => false,
            'available_from' => null,
            'available_until' => null,
        ];
    }
}
