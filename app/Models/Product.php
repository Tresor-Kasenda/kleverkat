<?php

namespace App\Models;

use App\Enums\ProductBillingFrequency;
use App\Enums\ProductPriceType;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sector_id',
    'code',
    'name',
    'slug',
    'short_description',
    'description',
    'price_type',
    'base_price',
    'currency',
    'billing_frequency',
    'min_age',
    'max_age',
    'min_insured_amount',
    'max_insured_amount',
    'duration_months',
    'waiting_period_days',
    'features',
    'exclusions',
    'sort_order',
    'is_active',
    'is_featured',
    'available_from',
    'available_until',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * Get the sector that owns the product.
     *
     * @return BelongsTo<Sector, $this>
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /** @return HasMany<Offer, $this> */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'price_type' => ProductPriceType::class,
            'billing_frequency' => ProductBillingFrequency::class,
            'base_price' => 'decimal:2',
            'min_insured_amount' => 'decimal:2',
            'max_insured_amount' => 'decimal:2',
            'features' => 'array',
            'exclusions' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'available_from' => 'date',
            'available_until' => 'date',
        ];
    }
}
