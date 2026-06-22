<?php

namespace App\Services\Comparison;

use App\Enums\OfferRuleType;
use App\Models\Offer;

class EligibilityEvaluator
{
    public function __construct(private readonly RuleMatcher $matcher) {}

    /**
     * Evaluate whether a user is eligible for an offer based on its eligibility rules.
     *
     * Returns true (eligible) only if ALL active eligibility rules are satisfied.
     * If no eligibility rules exist, the offer is eligible by default.
     *
     * @param  array<string, mixed>  $answers
     * @return array{eligible: bool, checks: array<int, array<string, mixed>>}
     */
    public function evaluate(Offer $offer, array $answers): array
    {
        $checks = [];
        $eligible = true;

        foreach ($offer->rules as $rule) {
            if ($rule->rule_type !== OfferRuleType::Eligibility || ! $rule->is_active) {
                continue;
            }

            $matched = $this->matcher->matches($rule, $answers);

            if (! $matched) {
                $eligible = false;
            }

            $checks[] = [
                'rule_id' => $rule->id,
                'field' => $rule->question->field_key,
                'operator' => $rule->operator->value,
                'expected' => $rule->expected_value,
                'answer' => $answers[$rule->question->field_key] ?? null,
                'matched' => $matched,
            ];
        }

        return ['eligible' => $eligible, 'checks' => $checks];
    }
}
