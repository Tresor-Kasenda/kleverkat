<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LeadActionType;
use App\Enums\LeadStatus;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /** @return array<model-property<Lead>, mixed> */
    public function definition(): array
    {
        return [
            'comparison_result_id' => null,
            'company_id' => Company::factory(),
            'offer_id' => Offer::factory(),
            'action_type' => $this->faker->randomElement(LeadActionType::cases())->value,
            'contact_first_name' => $this->faker->firstName(),
            'contact_last_name' => $this->faker->lastName(),
            'contact_email' => $this->faker->safeEmail(),
            'contact_phone' => $this->faker->optional()->numerify('0#########'),
            'status' => LeadStatus::New->value,
            'sent_at' => null,
        ];
    }

    public function quoteRequest(): static
    {
        return $this->state(['action_type' => LeadActionType::QuoteRequest->value]);
    }

    public function callback(): static
    {
        return $this->state(['action_type' => LeadActionType::Callback->value]);
    }

    public function partnerRedirect(): static
    {
        return $this->state(['action_type' => LeadActionType::PartnerRedirect->value]);
    }

    public function contacted(): static
    {
        return $this->state(['status' => LeadStatus::Contacted->value]);
    }

    public function qualified(): static
    {
        return $this->state(['status' => LeadStatus::Qualified->value]);
    }

    public function converted(): static
    {
        return $this->state(['status' => LeadStatus::Converted->value]);
    }

    public function lost(): static
    {
        return $this->state(['status' => LeadStatus::Lost->value]);
    }

    public function sent(): static
    {
        return $this->state(['sent_at' => now()]);
    }
}
