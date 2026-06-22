<?php

namespace App\Http\Controllers\Compare;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Sector;
use Inertia\Inertia;
use Inertia\Response;

class ProductListController extends Controller
{
    public function __invoke(Category $category, Sector $sector): Response
    {
        abort_if(! $category->is_active, 404);
        abort_if(! $sector->is_active || $sector->category_id !== $category->id, 404);

        return Inertia::render('Compare/ProductList', [
            'category' => $category,
            'sector'   => $sector,
            'products' => $sector->products()
                ->where('is_active', true)
                ->withCount(['offers' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('sort_order')
                ->get(),
        ]);
    }
}
