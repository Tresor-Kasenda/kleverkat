<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['sectors' => fn ($q) => $q->where('is_active', true)])
            ->with([
                'sectors' => fn ($q) => $q->where('is_active', true)
                    ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                    ->with([
                        'products' => fn ($q) => $q->where('is_active', true)
                            ->withCount(['offers' => fn ($q) => $q->where('is_active', true)])
                            ->orderBy('sort_order'),
                    ])
                    ->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Home', [
            'categories' => $categories,
        ]);
    }
}
