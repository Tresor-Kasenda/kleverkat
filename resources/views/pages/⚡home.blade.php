<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Sector;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component {
    public string $view = 'categories';

    /** @var Collection<int, Category> */
    public Collection $categories;

    public ?Category $selectedCategory = null;

    /** @var Collection<int, Sector> */
    public Collection $sectors;

    public ?Sector $selectedSector = null;

    /** @var Collection<int, Product> */
    public Collection $products;

    public bool $showAll = false;

    public function mount(): void
    {
        $this->categories = Category::query()
            ->where('is_active', true)
            ->withCount([
                'sectors' => fn($q) => $q->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->get();

        $this->sectors = collect();
        $this->products = collect();
    }

    public function browseCategory(int $categoryId): void
    {
        $this->selectedCategory = $this->categories->firstWhere('id', $categoryId);

        if ($this->selectedCategory === null) {
            return;
        }

        $this->sectors = $this->selectedCategory->sectors()
            ->where('is_active', true)
            ->withCount(['products' => fn($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $this->selectedSector = null;
        $this->products = collect();
        $this->showAll = false;
        $this->view = 'sectors';
    }

    public function browseSector(int $sectorId): void
    {
        $this->selectedSector = $this->sectors->firstWhere('id', $sectorId);

        if ($this->selectedSector === null) {
            return;
        }

        $this->products = $this->selectedSector->products()
            ->where('is_active', true)
            ->withCount(['offers' => fn($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $this->showAll = false;
        $this->view = 'products';
    }

    public function back(): void
    {
        if ($this->view === 'products') {
            $this->view = 'sectors';
            $this->selectedSector = null;
            $this->products = collect();
        } elseif ($this->view === 'sectors') {
            $this->view = 'categories';
            $this->selectedCategory = null;
            $this->sectors = collect();
        }
        $this->showAll = false;
    }

    public function render()
    {
        return $this->view()
            ->layout('layouts.home')
            ->title(config('app.name') . ' — Comparez et économisez');
    }
};
?>

<div>
    <div class="min-h-[92vh] bg-gradient-to-b from-sky-300 via-blue-400 to-blue-700 pb-16 pt-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Titre + fil d'Ariane ─────────────────────────────────────── --}}
            <div class="mb-8">
                @if ($view === 'categories')
                    <h1 class="text-3xl font-bold text-white sm:text-4xl">
                        <span class="mr-2 text-orange-400">✦</span>
                        Avec {{ config('app.name') }}, comparer c'est gagner
                    </h1>
                @else
                    {{-- Fil d'Ariane + bouton retour --}}
                    <div class="mb-5 flex items-center gap-3">
                        <button
                            wire:click="back"
                            class="flex items-center gap-1.5 rounded-full bg-white/20 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/30"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                            </svg>
                            Retour
                        </button>

                        <nav class="flex items-center gap-2 text-sm text-white/80">
                            <button wire:click="$set('view', 'categories')" class="hover:text-white transition-colors">
                                Accueil
                            </button>
                            @if ($selectedCategory)
                                <span class="text-white/50">/</span>
                                <button
                                    wire:click="$set('view', 'sectors')"
                                    class="{{ $view === 'sectors' ? 'text-white font-medium pointer-events-none' : 'hover:text-white transition-colors' }}"
                                >
                                    {{ $selectedCategory->name }}
                                </button>
                            @endif
                            @if ($selectedSector)
                                <span class="text-white/50">/</span>
                                <span class="text-white font-medium">{{ $selectedSector->name }}</span>
                            @endif
                        </nav>
                    </div>

                    <h1 class="text-2xl font-bold text-white sm:text-3xl">
                        @if ($view === 'sectors')
                            <span class="mr-2 text-orange-400">✦</span>{{ $selectedCategory->name }}
                        @else
                            <span class="mr-2 text-orange-400">✦</span>{{ $selectedSector->name }}
                        @endif
                    </h1>
                @endif
            </div>

            {{-- ── Grille de cartes ───────────────────────────────────────── --}}
            @php
                $items = match ($this->view) {
                    'sectors'  => $this->sectors,
                    'products' => $this->products,
                    default    => $this->categories,
                };
                $visibleItems = $this->showAll ? $items : $items->take(6);
                $hasMore = ! $this->showAll && $items->count() > 6;
            @endphp

            @if ($items->isEmpty())
                <div class="rounded-2xl bg-white/20 p-12 text-center text-white backdrop-blur-sm">
                    <p class="text-lg font-medium opacity-80">Aucun élément disponible pour le moment.</p>
                </div>
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-3">
                    @foreach ($visibleItems as $item)
                        @php
                            $idx = $loop->index;
                            // Palette couleurs pour les icônes
                            $palettes = [
                                ['bg' => 'bg-orange-100',  'icon' => 'text-orange-500'],
                                ['bg' => 'bg-blue-100',    'icon' => 'text-blue-500'],
                                ['bg' => 'bg-emerald-100', 'icon' => 'text-emerald-500'],
                                ['bg' => 'bg-violet-100',  'icon' => 'text-violet-500'],
                                ['bg' => 'bg-rose-100',    'icon' => 'text-rose-500'],
                                ['bg' => 'bg-amber-100',   'icon' => 'text-amber-500'],
                            ];
                            $palette = $palettes[$idx % count($palettes)];

                            // Icônes selon la vue
                            $icons = [
                                // catégories
                                0 => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />',
                                1 => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819" />',
                                2 => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />',
                                3 => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 3h3m-3 3h3" />',
                                4 => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />',
                                5 => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />',
                            ];
                            $iconPath = $icons[$idx % count($icons)];
                        @endphp

                        @if ($view === 'products')
                            {{-- Carte produit → lien direct vers le wizard --}}
                            <a
                                href="{{ route('compare.wizard', [$selectedCategory->slug, $selectedSector->slug, $item->slug]) }}"
                                wire:navigate
                                wire:key="item-{{ $item->id }}"
                                class="group flex flex-col items-center rounded-2xl bg-white p-6 text-center shadow-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-500"
                            >
                                <div
                                    class="mb-4 flex size-16 items-center justify-center rounded-2xl {{ $palette['bg'] }}">
                                    <svg class="size-8 {{ $palette['icon'] }}" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="1.5">
                                        {!! $iconPath !!}
                                    </svg>
                                </div>
                                <h3 class="mb-1 text-sm font-semibold text-zinc-900 sm:text-base">{{ $item->name }}</h3>
                                @if ($item->offers_count > 0)
                                    <p class="text-xs font-medium text-blue-600 sm:text-sm">
                                        {{ $item->offers_count }} offre{{ $item->offers_count > 1 ? 's' : '' }}
                                        comparée{{ $item->offers_count > 1 ? 's' : '' }}
                                    </p>
                                @else
                                    <p class="text-xs text-zinc-400">Bientôt disponible</p>
                                @endif
                            </a>
                        @else
                            {{-- Carte catégorie ou secteur → drill-down --}}
                            <button
                                wire:click="{{ $view === 'categories' ? 'browseCategory' : 'browseSector' }}({{ $item->id }})"
                                wire:key="item-{{ $item->id }}"
                                class="group flex flex-col items-center rounded-2xl bg-white p-6 text-center shadow-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-500"
                            >
                                <div
                                    class="mb-4 flex size-16 items-center justify-center rounded-2xl {{ $palette['bg'] }}">
                                    <svg class="size-8 {{ $palette['icon'] }}" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="1.5">
                                        {!! $iconPath !!}
                                    </svg>
                                </div>
                                <h3 class="mb-1 text-sm font-semibold text-zinc-900 sm:text-base">{{ $item->name }}</h3>
                                @php
                                    $count = $view === 'categories' ? $item->sectors_count : $item->products_count;
                                    $label = $view === 'categories' ? 'secteur' : 'produit';
                                @endphp
                                @if ($count > 0)
                                    <p class="text-xs font-medium text-blue-600 sm:text-sm">
                                        {{ $count }} {{ $label }}{{ $count > 1 ? 's' : '' }}
                                    </p>
                                @endif
                            </button>
                        @endif
                    @endforeach
                </div>

                {{-- "Voir plus" --}}
                @if ($hasMore)
                    <div class="mt-8 text-center">
                        <button
                            wire:click="$set('showAll', true)"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-white transition hover:text-white/80"
                        >
                            Voir plus
                            de {{ $view === 'categories' ? 'catégories' : ($view === 'sectors' ? 'secteurs' : 'produits') }}
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>
                    </div>
                @endif
            @endif

        </div>
    </div>

    {{-- Footer minimal --}}
    <footer class="border-t border-zinc-100 bg-white py-6 text-center text-sm text-zinc-400">
        © {{ date('Y') }} {{ config('app.name') }} — Comparateur en ligne gratuit
    </footer>
</div>
