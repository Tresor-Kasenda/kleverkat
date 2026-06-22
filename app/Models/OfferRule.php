<?php

namespace App\Models;

use App\Enums\OfferRuleOperator;
use App\Enums\OfferRuleType;
use Database\Factories\OfferRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property OfferRuleType $rule_type
 * @property OfferRuleOperator $operator
 */
#[Fillable([
    'offer_id',
    'question_id',
    'rule_type',
    'operator',
    'expected_value',
    'weight',
    'score_delta',
    'price_delta',
    'price_multiplier',
    'priority',
    'is_active',
])]
class OfferRule extends Model
{
    /** @use HasFactory<OfferRuleFactory> */
    use HasFactory;

    /** @return BelongsTo<Offer, $this> */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /** @return BelongsTo<Question, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'rule_type' => OfferRuleType::class,
            'operator' => OfferRuleOperator::class,
            'weight' => 'decimal:3',
            'score_delta' => 'decimal:2',
            'price_delta' => 'decimal:2',
            'price_multiplier' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }
}
