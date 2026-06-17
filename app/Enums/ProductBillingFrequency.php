<?php

namespace App\Enums;

enum ProductBillingFrequency: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case SemiAnnual = 'semi_annual';
    case Annual = 'annual';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Mensuelle',
            self::Quarterly => 'Trimestrielle',
            self::SemiAnnual => 'Semestrielle',
            self::Annual => 'Annuelle',
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
