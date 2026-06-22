<?php

namespace App\Models;

use App\Enums\QuestionInputType;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'questionnaire_id',
    'step_key',
    'field_key',
    'label',
    'input_type',
    'options_json',
    'validation_rules_json',
    'display_conditions_json',
    'placeholder',
    'helper_text',
    'sort_order',
    'is_required',
    'is_active',
])]
class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    /** @return BelongsTo<Questionnaire, $this> */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'input_type' => QuestionInputType::class,
            'options_json' => 'array',
            'validation_rules_json' => 'array',
            'display_conditions_json' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
