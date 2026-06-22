<?php

namespace App\Models;

use Database\Factories\ComparisonSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property array<string, mixed> $answers_json
 */
#[Fillable([
    'product_id',
    'questionnaire_id',
    'user_id',
    'answers_json',
    'started_at',
    'completed_at',
    'ip_address',
    'user_agent',
])]
class ComparisonSession extends Model
{
    /** @use HasFactory<ComparisonSessionFactory> */
    use HasFactory;

    use HasUuids;

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<Questionnaire, $this> */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<ComparisonResult, $this> */
    public function results(): HasMany
    {
        return $this->hasMany(ComparisonResult::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'answers_json' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
