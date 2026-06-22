<?php

namespace App\Enums;

enum QuestionInputType: string
{
    case Text = 'text';
    case Number = 'number';
    case Select = 'select';
    case Radio = 'radio';
    case Checkbox = 'checkbox';
    case Date = 'date';
    case Boolean = 'boolean';
    case Textarea = 'textarea';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Texte libre',
            self::Number => 'Nombre',
            self::Select => 'Liste déroulante',
            self::Radio => 'Choix unique (radio)',
            self::Checkbox => 'Choix multiple (checkbox)',
            self::Date => 'Date',
            self::Boolean => 'Oui / Non',
            self::Textarea => 'Texte long',
        };
    }

    public function hasOptions(): bool
    {
        return in_array($this, [self::Select, self::Radio, self::Checkbox], strict: true);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
