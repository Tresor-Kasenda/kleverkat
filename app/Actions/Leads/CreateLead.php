<?php

declare(strict_types=1);

namespace App\Actions\Leads;

use App\Enums\LeadActionType;
use App\Enums\LeadStatus;
use App\Models\ComparisonResult;
use App\Models\Lead;

class CreateLead
{
    /**
     * Create a lead from a comparison result when a user initiates a commercial action.
     *
     * @param  array{first_name: string, last_name: string, email: string, phone?: string|null}  $contactData
     */
    public function handle(ComparisonResult $result, array $contactData, LeadActionType $actionType): Lead
    {
        return Lead::create([
            'comparison_result_id' => $result->id,
            'company_id' => $result->company_id,
            'offer_id' => $result->offer_id,
            'action_type' => $actionType->value,
            'contact_first_name' => $contactData['first_name'],
            'contact_last_name' => $contactData['last_name'],
            'contact_email' => $contactData['email'],
            'contact_phone' => $contactData['phone'] ?? null,
            'status' => LeadStatus::New->value,
        ]);
    }
}
