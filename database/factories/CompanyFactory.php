<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'sector_id' => Sector::factory(),
            'team_id' => null,
            'manager_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'logo_path' => fake()->boolean(30) ? 'logos/' . Str::slug($name) . '.png' : null,
            'description' => fake()->paragraph(),
            'website_url' => fake()->url(),
            'support_email' => fake()->companyEmail(),
            'support_phone' => fake()->phoneNumber(),
            'contact_name' => fake()->name(),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->boolean(35) ? fake()->secondaryAddress() : null,
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->country(),
            'is_active' => true,
        ];
    }
}
