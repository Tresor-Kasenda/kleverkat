<?php

namespace App\Services\Comparison;

use App\Enums\OfferRuleType;
use App\Models\Offer;

class PricingEvaluator
{
    public function __construct(private readonly RuleMatcher $matcher) {}

    /**
     * Calculate the final price for an offer after applying matched pricing rules.
     *
     * Rules are applied in priority order:
     *   1. price_delta is added to the running price
     *   2. price_multiplier is applied to the running price
     *
     * Returns null when the offer has no base price (on-quote pricing).
     *
     * @param  array<string, mixed>  $answers
     * @return array{price: float|null, adjustments: array<int, array<string, mixed>>}
     */
    public function evaluate(Offer $offer, array $answers): array
    {
        $basePrice = $offer->base_price !== null ? (float) $offer->base_price : null;

        if ($basePrice === null) {
            return ['price' => null, 'adjustments' => []];
        }

        $price = $basePrice;
        $adjustments = [];

        foreach ($offer->rules as $rule) {
            if ($rule->rule_type !== OfferRuleType::Pricing || ! $rule->is_active) {
                continue;
            }

            $matched = $this->matcher->matches($rule, $answers);

            if ($matched) {
                if ($rule->price_delta !== null) {
                    $price += (float) $rule->price_delta;
                }

                if ($rule->price_multiplier !== null) {
                    $price *= (float) $rule->price_multiplier;
                }
            }

            $adjustments[] = [
                'rule_id' => $rule->id,
                'field' => $rule->question->field_key,
                'operator' => $rule->operator->value,
                'expected' => $rule->expected_value,
                'answer' => $answers[$rule->question->field_key] ?? null,
                'matched' => $matched,
                'price_delta' => (float) ($rule->price_delta ?? 0),
                'price_multiplier' => (float) ($rule->price_multiplier ?? 1.0),
            ];
        }

        return ['price' => round($price, 2), 'adjustments' => $adjustments];
    }
}
