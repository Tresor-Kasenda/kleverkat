<?php

namespace App\Enums;

enum OfferRuleOperator: string
{
    case Eq = 'eq';
    case Neq = 'neq';
    case Lt = 'lt';
    case Lte = 'lte';
    case Gt = 'gt';
    case Gte = 'gte';
    case In = 'in';
    case NotIn = 'not_in';

    public function label(): string
    {
        return match ($this) {
            self::Eq => '= égal à',
            self::Neq => '≠ différent de',
            self::Lt => '< strictement inférieur à',
            self::Lte => '≤ inférieur ou égal à',
            self::Gt => '> strictement supérieur à',
            self::Gte => '≥ supérieur ou égal à',
            self::In => 'dans la liste (valeurs séparées par ,)',
            self::NotIn => 'pas dans la liste (valeurs séparées par ,)',
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
