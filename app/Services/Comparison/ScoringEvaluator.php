<?php

namespace App\Services\Comparison;

use App\Enums\OfferRuleType;
use App\Models\Offer;

class ScoringEvaluator
{
    public function __construct(private readonly RuleMatcher $matcher) {}

    /**
     * Calculate the score for an offer based on matching scoring rules.
     *
     * Score = Σ (score_delta × weight) for each matched active scoring rule.
     *
     * @param  array<string, mixed>  $answers
     * @return array{score: float, contributions: array<int, array<string, mixed>>}
     */
    public function evaluate(Offer $offer, array $answers): array
    {
        $score = 0.0;
        $contributions = [];

        foreach ($offer->rules as $rule) {
            if ($rule->rule_type !== OfferRuleType::Scoring || ! $rule->is_active) {
                continue;
            }

            $matched = $this->matcher->matches($rule, $answers);
            $contribution = 0.0;

            if ($matched && $rule->score_delta !== null) {
                $contribution = (float) $rule->score_delta * (float) ($rule->weight ?? 1.0);
                $score += $contribution;
            }

            $contributions[] = [
                'rule_id' => $rule->id,
                'field' => $rule->question->field_key,
                'operator' => $rule->operator->value,
                'expected' => $rule->expected_value,
                'answer' => $answers[$rule->question->field_key] ?? null,
                'matched' => $matched,
                'delta' => (float) ($rule->score_delta ?? 0),
                'weight' => (float) ($rule->weight ?? 1.0),
                'contribution' => $contribution,
            ];
        }

        return ['score' => round($score, 2), 'contributions' => $contributions];
    }
}
