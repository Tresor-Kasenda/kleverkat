<?php

namespace Database\Factories;

use App\Enums\QuestionInputType;
use App\Models\Question;
use App\Models\Questionnaire;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * @return array<model-property<Question>, mixed>
     */
    public function definition(): array
    {
        $inputType = fake()->randomElement(QuestionInputType::cases());

        return [
            'questionnaire_id' => Questionnaire::factory(),
            'step_key' => fake()->randomElement(['profil', 'vehicule', 'conducteur', 'historique']),
            'field_key' => fake()->unique()->word(),
            'label' => fake()->sentence(5, true).'?',
            'input_type' => $inputType,
            'options_json' => $inputType->hasOptions() ? ['oui' => 'Oui', 'non' => 'Non'] : null,
            'validation_rules_json' => ['required'],
            'display_conditions_json' => null,
            'placeholder' => null,
            'helper_text' => null,
            'sort_order' => 0,
            'is_required' => true,
            'is_active' => true,
        ];
    }

    public function text(): static
    {
        return $this->state(['input_type' => QuestionInputType::Text, 'options_json' => null]);
    }

    public function radio(): static
    {
        return $this->state(['input_type' => QuestionInputType::Radio, 'options_json' => ['oui' => 'Oui', 'non' => 'Non']]);
    }

    public function number(): static
    {
        return $this->state(['input_type' => QuestionInputType::Number, 'options_json' => null]);
    }
}
