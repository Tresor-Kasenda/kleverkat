<?php

namespace App\Models;

use Database\Factories\OfferFeatureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'offer_id',
    'label',
    'value',
    'is_highlight',
    'sort_order',
])]
class OfferFeature extends Model
{
    /** @use HasFactory<OfferFeatureFactory> */
    use HasFactory;

    /** @return BelongsTo<Offer, $this> */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    protected function casts(): array
    {
        return [
            'is_highlight' => 'boolean',
        ];
    }
}
