<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadActionType;
use App\Enums\LeadStatus;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property LeadStatus $status
 * @property LeadActionType $action_type
 */
#[Fillable([
    'comparison_result_id',
    'company_id',
    'offer_id',
    'action_type',
    'contact_first_name',
    'contact_last_name',
    'contact_email',
    'contact_phone',
    'status',
    'sent_at',
])]
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    /** @return BelongsTo<ComparisonResult, $this> */
    public function result(): BelongsTo
    {
        return $this->belongsTo(ComparisonResult::class, 'comparison_result_id');
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Offer, $this> */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action_type' => LeadActionType::class,
            'status' => LeadStatus::class,
            'sent_at' => 'datetime',
        ];
    }
}
