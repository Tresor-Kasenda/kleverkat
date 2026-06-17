<?php

namespace App\Enums;

enum ProductCategory: string
{
    case Vie = 'vie';
    case Sante = 'sante';
    case Auto = 'auto';
    case Habitation = 'habitation';
    case ResponsabiliteCivile = 'rc';
    case Voyage = 'voyage';
    case Agricole = 'agricole';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Vie => 'Vie',
            self::Sante => 'Santé',
            self::Auto => 'Auto',
            self::Habitation => 'Habitation',
            self::ResponsabiliteCivile => 'Responsabilité civile',
            self::Voyage => 'Voyage',
            self::Agricole => 'Agricole',
            self::Autre => 'Autre',
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
