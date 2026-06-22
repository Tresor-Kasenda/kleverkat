<?php

namespace App\Models;

use Database\Factories\ComparisonResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property array{eligibility: array<int, array<string, mixed>>, scoring: array<int, array<string, mixed>>, pricing: array<int, array<string, mixed>>} $explanation_json
 */
#[Fillable([
    'comparison_session_id',
    'offer_id',
    'company_id',
    'is_eligible',
    'score',
    'calculated_price',
    'explanation_json',
    'rank_position',
])]
class ComparisonResult extends Model
{
    /** @use HasFactory<ComparisonResultFactory> */
    use HasFactory;

    /** @return BelongsTo<ComparisonSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ComparisonSession::class, 'comparison_session_id');
    }

    /** @return BelongsTo<Offer, $this> */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasOne<Lead, $this> */
    public function lead(): HasOne
    {
        return $this->hasOne(Lead::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_eligible' => 'boolean',
            'score' => 'decimal:2',
            'calculated_price' => 'decimal:2',
            'explanation_json' => 'array',
        ];
    }
}
