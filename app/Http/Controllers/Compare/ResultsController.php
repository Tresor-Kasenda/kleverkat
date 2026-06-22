<?php

namespace App\Http\Controllers\Compare;

use App\Actions\Leads\CreateLead;
use App\Enums\LeadActionType;
use App\Http\Controllers\Controller;
use App\Models\ComparisonResult;
use App\Models\ComparisonSession;
use App\Services\Comparison\ComparisonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResultsController extends Controller
{
    public function show(ComparisonSession $session, ComparisonService $service): Response
    {
        abort_if(! $session->isCompleted(), 404);

        $session->load('product.sector.category');

        $results = $service->rankedByPrice($session)->map(fn ($r) => [
            'id'    => $r->id,
            'score' => $r->score,
            'price' => $r->calculated_price,
            'offer' => [
                'id'                => $r->offer->id,
                'name'              => $r->offer->name,
                'short_description' => $r->offer->short_description,
                'price_note'        => $r->offer->price_note,
                'is_featured'       => $r->offer->is_featured,
                'features'          => $r->offer->features->map(fn ($f) => [
                    'label'        => $f->label,
                    'value'        => $f->value,
                    'is_highlight' => $f->is_highlight,
                ])->all(),
                'company' => [
                    'name'        => $r->offer->company->name,
                    'website_url' => $r->offer->company->website_url,
                ],
            ],
        ])->all();

        return Inertia::render('Compare/Results', [
            'session' => [
                'id'      => $session->id,
                'product' => [
                    'name' => $session->product->name,
                    'sector' => [
                        'name' => $session->product->sector->name,
                        'category' => [
                            'name' => $session->product->sector->category->name,
                            'slug' => $session->product->sector->category->slug,
                        ],
                    ],
                ],
            ],
            'results' => $results,
        ]);
    }

    public function createLead(Request $request, ComparisonResult $result, CreateLead $action): JsonResponse
    {
        $data = $request->validate([
            'first_name'  => ['required', 'string', 'max:255'],
            'last_name'   => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'action_type' => ['required', 'string', 'in:quote_request,callback,partner_redirect'],
        ]);

        $actionType = LeadActionType::from($data['action_type']);

        $action->handle($result, [
            'contact_first_name' => $data['first_name'],
            'contact_last_name'  => $data['last_name'],
            'contact_email'      => $data['email'],
            'contact_phone'      => $data['phone'] ?? null,
        ], $actionType);

        return response()->json(['success' => true]);
    }
}
