<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Company;
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
     * @return array<model-property<Company>, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'category_id' => Category::factory(),
            'team_id' => null,
            'manager_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'logo_path' => $this->faker->boolean(30) ? 'logos/'.Str::slug($name).'.png' : null,
            'description' => $this->faker->paragraph(),
            'website_url' => $this->faker->url(),
            'support_email' => $this->faker->companyEmail(),
            'support_phone' => $this->faker->numerify('+243 ### ### ###'),
            'contact_name' => $this->faker->name(),
            'address_line_1' => $this->faker->streetAddress(),
            'address_line_2' => $this->faker->boolean(35) ? $this->faker->streetAddress() : null,
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'is_active' => true,
        ];
    }
}
