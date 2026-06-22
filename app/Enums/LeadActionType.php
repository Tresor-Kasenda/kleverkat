<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadActionType: string
{
    case QuoteRequest = 'quote_request';
    case Callback = 'callback';
    case PartnerRedirect = 'partner_redirect';

    public function label(): string
    {
        return match ($this) {
            self::QuoteRequest => 'Demander un devis',
            self::Callback => 'Être rappelé',
            self::PartnerRedirect => 'Voir l\'offre',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::QuoteRequest => 'heroicon-o-document-text',
            self::Callback => 'heroicon-o-phone',
            self::PartnerRedirect => 'heroicon-o-arrow-top-right-on-square',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::QuoteRequest => 'primary',
            self::Callback => 'warning',
            self::PartnerRedirect => 'gray',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_column(
            array_map(fn (self $case): array => [$case->value, $case->label()], self::cases()),
            1,
            0,
        );
    }
}
