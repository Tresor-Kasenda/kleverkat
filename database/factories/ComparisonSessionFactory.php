<?php

namespace Database\Factories;

use App\Models\ComparisonSession;
use App\Models\Product;
use App\Models\Questionnaire;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComparisonSession>
 */
class ComparisonSessionFactory extends Factory
{
    /**
     * @return array<model-property<ComparisonSession>, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'product_id' => $product->id,
            'questionnaire_id' => Questionnaire::factory()->state(['product_id' => $product->id]),
            'user_id' => null,
            'answers_json' => [],
            'started_at' => now(),
            'completed_at' => null,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }

    public function completed(): static
    {
        return $this->state(['completed_at' => now()]);
    }

    /**
     * @param  array<string, mixed>  $answers
     */
    public function withAnswers(array $answers): static
    {
        return $this->state(['answers_json' => $answers]);
    }
}
