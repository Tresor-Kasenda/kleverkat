<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Sector;
use Livewire\Component;

new class extends Component
{
    public Category $category;

    public Sector $sector;

    /** @var \Illuminate\Database\Eloquent\Collection<int, Product> */
    public $products;

    public function mount(Category $category, Sector $sector): void
    {
        abort_if(! $category->is_active, 404);
        abort_if(! $sector->is_active || $sector->category_id !== $category->id, 404);

        $this->category = $category;
        $this->sector = $sector;

        $this->products = $sector->products()
            ->where('is_active', true)
            ->withCount(['offers' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();
    }

    public function render()
    {
        return $this->view()
            ->layout('layouts.compare')
            ->title($this->sector->name . ' — ' . config('app.name'));
    }
};
?>

<div>
    <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
        <a href="{{ route('compare.categories') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
            Catégories
        </a>
        <flux:icon.chevron-right class="size-4" />
        <a href="{{ route('compare.sectors', $category->slug) }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
            {{ $category->name }}
        </a>
        <flux:icon.chevron-right class="size-4" />
        <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ $sector->name }}</span>
    </nav>

    <div class="mb-8">
        <flux:heading size="2xl" class="mb-2">{{ $sector->name }}</flux:heading>
        @if ($sector->description)
            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $sector->description }}</flux:text>
        @endif
    </div>

    @if ($products->isEmpty())
        <flux:callout icon="information-circle">
            <flux:callout.heading>Aucun produit disponible</flux:callout.heading>
            <flux:callout.text>Aucun produit comparable n'est encore disponible dans ce secteur.</flux:callout.text>
        </flux:callout>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                <div class="flex flex-col rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 shadow-sm">
                    <div class="mb-3 flex items-start justify-between">
                        <flux:heading size="lg">{{ $product->name }}</flux:heading>
                        @if ($product->offers_count > 0)
                            <flux:badge color="blue" size="sm">{{ $product->offers_count }} offre{{ $product->offers_count > 1 ? 's' : '' }}</flux:badge>
                        @endif
                    </div>

                    @if ($product->description)
                        <flux:text class="mb-4 text-sm text-zinc-500 dark:text-zinc-400 flex-1 line-clamp-3">
                            {{ $product->description }}
                        </flux:text>
                    @endif

                    <flux:button
                        href="{{ route('compare.wizard', [$category->slug, $sector->slug, $product->slug]) }}"
                        wire:navigate
                        variant="primary"
                        class="mt-4 w-full"
                    >
                        Comparer les offres
                    </flux:button>
                </div>
            @endforeach
        </div>
    @endif
</div>
