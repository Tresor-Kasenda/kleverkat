<?php

namespace App\Enums;

enum ProductPriceType: string
{
    case Fixed = 'fixed';
    case Variable = 'variable';
    case OnQuote = 'on_quote';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixe',
            self::Variable => 'Variable',
            self::OnQuote => 'Sur devis',
        };
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
