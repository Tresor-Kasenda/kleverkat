<?php

namespace App\Http\Controllers\Compare;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class CategoryListController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Compare/CategoryList', [
            'categories' => Category::query()
                ->where('is_active', true)
                ->withCount(['sectors' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('sort_order')
                ->get(),
        ]);
    }
}
