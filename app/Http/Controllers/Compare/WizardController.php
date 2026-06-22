<?php

namespace App\Http\Controllers\Compare;

use App\Enums\QuestionInputType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ComparisonSession;
use App\Models\Product;
use App\Models\Sector;
use App\Services\Comparison\ComparisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WizardController extends Controller
{
    public function show(Category $category, Sector $sector, Product $product): Response
    {
        abort_if(! $category->is_active, 404);
        abort_if(! $sector->is_active || $sector->category_id !== $category->id, 404);
        abort_if(! $product->is_active || $product->sector_id !== $sector->id, 404);

        $questionnaire = $product->questionnaires()
            ->where('is_active', true)
            ->with(['questions' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->first();

        abort_if($questionnaire === null, 404);

        $steps = collect($questionnaire->questions)
            ->groupBy('step_key')
            ->map(fn ($qs) => $qs->values()->map(fn ($q) => [
                'id'          => $q->id,
                'field_key'   => $q->field_key,
                'label'       => $q->label,
                'input_type'  => $q->input_type->value,
                'options'     => $q->options_json,
                'placeholder' => $q->placeholder,
                'helper_text' => $q->helper_text,
                'is_required' => $q->is_required,
                'has_options' => $q->input_type->hasOptions(),
            ])->all())
            ->all();

        return Inertia::render('Compare/Wizard', [
            'category'    => $category,
            'sector'      => $sector,
            'product'     => $product,
            'steps'       => $steps,
            'stepKeys'    => array_keys($steps),
        ]);
    }

    public function store(Request $request, Category $category, Sector $sector, Product $product, ComparisonService $service): RedirectResponse
    {
        abort_if(! $category->is_active, 404);
        abort_if(! $sector->is_active || $sector->category_id !== $category->id, 404);
        abort_if(! $product->is_active || $product->sector_id !== $sector->id, 404);

        $questionnaire = $product->questionnaires()
            ->where('is_active', true)
            ->with(['questions' => fn ($q) => $q->where('is_active', true)])
            ->first();

        abort_if($questionnaire === null, 404);

        $session = ComparisonSession::create([
            'product_id'       => $product->id,
            'questionnaire_id' => $questionnaire->id,
            'user_id'          => $request->user()?->id,
            'answers_json'     => $request->input('answers', []),
        ]);

        $service->compare($session);

        return redirect()->route('compare.results', $session);
    }
}
