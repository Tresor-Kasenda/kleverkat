<?php

namespace App\Http\Middleware;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ] : null,
                'currentTeam' => $request->user()?->currentTeam ? [
                    'id' => $request->user()->currentTeam->id,
                    'name' => $request->user()->currentTeam->name,
                    'slug' => $request->user()->currentTeam->slug ?? null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function shareOnce(Request $request): array
    {
        return [
            ...parent::shareOnce($request),
            'navCategories' => fn () => Category::query()
                ->where('is_active', true)
                ->with([
                    'sectors' => fn ($query) => $query
                        ->select(['id', 'category_id', 'name', 'slug', 'description', 'sort_order'])
                        ->where('is_active', true)
                        ->with([
                            'products' => fn ($query) => $query
                                ->select(['id', 'sector_id', 'name', 'slug', 'short_description', 'sort_order'])
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->limit(7),
                        ])
                        ->orderBy('sort_order'),
                ])
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'description']),
        ];
    }
}
