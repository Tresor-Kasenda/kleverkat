<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'category_id',
    'team_id',
    'manager_id',
    'name',
    'slug',
    'logo_path',
    'description',
    'website_url',
    'support_email',
    'support_phone',
    'contact_name',
    'address_line_1',
    'address_line_2',
    'city',
    'postal_code',
    'country',
    'is_active',
])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    /**
     * Get the category that owns the company.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the partner team linked to the company.
     *
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the manager assigned to this company.
     *
     * @return BelongsTo<User, $this>
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Determine whether the given user is the assigned manager.
     */
    public function isManagedBy(User $user): bool
    {
        return $this->manager_id === $user->id;
    }

    /**
     * Get the human-readable profile fields that are still missing.
     *
     * @return array<int, string>
     */
    public function missingProfileFields(): array
    {
        return collect([
            'description' => 'Description',
            'website_url' => 'Site web',
            'support_email' => 'Email support',
            'support_phone' => 'Téléphone support',
            'contact_name' => 'Contact principal',
            'address_line_1' => 'Adresse',
            'city' => 'Ville',
            'country' => 'Pays',
        ])
            ->filter(fn (string $label, string $attribute): bool => blank($this->getAttribute($attribute)))
            ->values()
            ->all();
    }

    /**
     * Determine whether the public company profile is complete enough.
     */
    public function isProfileComplete(): bool
    {
        return $this->missingProfileFields() === [];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
