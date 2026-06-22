<?php

namespace App\Http\Controllers\Compare;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class SectorListController extends Controller
{
    public function __invoke(Category $category): Response
    {
        abort_if(! $category->is_active, 404);

        return Inertia::render('Compare/SectorList', [
            'category' => $category,
            'sectors'  => $category->sectors()
                ->where('is_active', true)
                ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('sort_order')
                ->get(),
        ]);
    }
}
