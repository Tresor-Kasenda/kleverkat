<?php

namespace App\Services\Comparison;

use App\Models\ComparisonResult;
use App\Models\ComparisonSession;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Collection;

class ComparisonService
{
    public function __construct(
        private readonly EligibilityEvaluator $eligibility,
        private readonly ScoringEvaluator $scoring,
        private readonly PricingEvaluator $pricing,
    ) {}

    /**
     * Run the comparison engine for a session.
     *
     * Loads all active offers for the session's product, evaluates each one
     * against the session's answers, persists ComparisonResult records, then
     * ranks eligible offers by score (descending) and marks the session completed.
     */
    public function compare(ComparisonSession $session): void
    {
        $offers = Offer::query()
            ->where('product_id', $session->product_id)
            ->where('is_active', true)
            ->with(['rules' => fn ($q) => $q->with('question')->where('is_active', true)->orderBy('priority'), 'company'])
            ->get();

        $answers = $session->answers_json;
        $created = [];

        foreach ($offers as $offer) {
            $eligibilityResult = $this->eligibility->evaluate($offer, $answers);
            $isEligible = $eligibilityResult['eligible'];

            $scoringResult = $isEligible
                ? $this->scoring->evaluate($offer, $answers)
                : ['score' => 0.0, 'contributions' => []];

            $pricingResult = $isEligible
                ? $this->pricing->evaluate($offer, $answers)
                : ['price' => null, 'adjustments' => []];

            $created[] = ComparisonResult::create([
                'comparison_session_id' => $session->id,
                'offer_id' => $offer->id,
                'company_id' => $offer->company_id,
                'is_eligible' => $isEligible,
                'score' => $isEligible ? $scoringResult['score'] : null,
                'calculated_price' => $pricingResult['price'],
                'explanation_json' => [
                    'eligibility' => $eligibilityResult['checks'],
                    'scoring' => $scoringResult['contributions'],
                    'pricing' => $pricingResult['adjustments'],
                ],
                'rank_position' => null,
            ]);
        }

        // Rank eligible results by score descending
        collect($created)
            ->filter(fn (ComparisonResult $r): bool => $r->is_eligible)
            ->sortByDesc(fn (ComparisonResult $r): float => (float) ($r->score ?? 0))
            ->values()
            ->each(function (ComparisonResult $r, int $index): void {
                $r->update(['rank_position' => $index + 1]);
            });

        $session->update(['completed_at' => now()]);
    }

    /**
     * Return the ranked eligible results for a completed session, best score first.
     *
     * @return Collection<int, ComparisonResult>
     */
    public function rankedResults(ComparisonSession $session): Collection
    {
        return $session->results()
            ->with(['offer.company', 'offer.features'])
            ->where('is_eligible', true)
            ->whereNotNull('rank_position')
            ->orderBy('rank_position')
            ->get();
    }

    /**
     * Return eligible results ordered by calculated price ascending (cheapest first).
     * Offers without a price (on-quote) are appended after priced offers.
     *
     * @return Collection<int, ComparisonResult>
     */
    public function rankedByPrice(ComparisonSession $session): Collection
    {
        $withPrice = $session->results()
            ->with(['offer.company', 'offer.features'])
            ->where('is_eligible', true)
            ->whereNotNull('calculated_price')
            ->orderBy('calculated_price', 'asc')
            ->get();

        $onQuote = $session->results()
            ->with(['offer.company', 'offer.features'])
            ->where('is_eligible', true)
            ->whereNull('calculated_price')
            ->get();

        return $withPrice->concat($onQuote);
    }
}
