<?php

namespace App\Services\Comparison;

use App\Enums\OfferRuleOperator;
use App\Models\OfferRule;

class RuleMatcher
{
    /**
     * Evaluate whether an offer rule condition is satisfied by the given answers.
     *
     * @param  array<string, mixed>  $answers
     */
    public function matches(OfferRule $rule, array $answers): bool
    {
        $fieldKey = $rule->question->field_key;
        $answer = $answers[$fieldKey] ?? null;
        $expected = $rule->expected_value;

        return match ($rule->operator) {
            OfferRuleOperator::Eq => $this->equals($answer, $expected),
            OfferRuleOperator::Neq => ! $this->equals($answer, $expected),
            OfferRuleOperator::Lt => is_numeric($answer) && (float) $answer < (float) $expected,
            OfferRuleOperator::Lte => is_numeric($answer) && (float) $answer <= (float) $expected,
            OfferRuleOperator::Gt => is_numeric($answer) && (float) $answer > (float) $expected,
            OfferRuleOperator::Gte => is_numeric($answer) && (float) $answer >= (float) $expected,
            OfferRuleOperator::In => $this->inList($answer, $expected),
            OfferRuleOperator::NotIn => ! $this->inList($answer, $expected),
        };
    }

    private function equals(mixed $answer, string $expected): bool
    {
        if (is_array($answer)) {
            return in_array($expected, $answer, strict: true);
        }

        return (string) $answer === $expected;
    }

    private function inList(mixed $answer, string $expected): bool
    {
        $list = array_map('trim', explode(',', $expected));

        if (is_array($answer)) {
            return ! empty(array_intersect($answer, $list));
        }

        return in_array((string) $answer, $list, strict: true);
    }
}
