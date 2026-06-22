<?php

namespace App\Enums;

enum OfferRuleType: string
{
    case Eligibility = 'eligibility';
    case Scoring = 'scoring';
    case Pricing = 'pricing';

    public function label(): string
    {
        return match ($this) {
            self::Eligibility => 'Éligibilité',
            self::Scoring => 'Scoring',
            self::Pricing => 'Tarification',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Eligibility => 'danger',
            self::Scoring => 'info',
            self::Pricing => 'warning',
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
