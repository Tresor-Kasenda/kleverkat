<?php

use App\Models\Category;
use App\Models\Sector;
use Livewire\Component;

new class extends Component
{
    public Category $category;

    /** @var \Illuminate\Database\Eloquent\Collection<int, Sector> */
    public $sectors;

    public function mount(Category $category): void
    {
        abort_if(! $category->is_active, 404);

        $this->category = $category;

        $this->sectors = $category->sectors()
            ->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();
    }

    public function render()
    {
        return $this->view()
            ->layout('layouts.compare')
            ->title($this->category->name . ' — ' . config('app.name'));
    }
};
?>

<div>
    <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
        <a href="{{ route('compare.categories') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
            Catégories
        </a>
        <flux:icon.chevron-right class="size-4" />
        <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ $category->name }}</span>
    </nav>

    <div class="mb-8">
        <flux:heading size="2xl" class="mb-2">{{ $category->name }}</flux:heading>
        @if ($category->description)
            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $category->description }}</flux:text>
        @endif
    </div>

    @if ($sectors->isEmpty())
        <flux:callout icon="information-circle">
            <flux:callout.heading>Aucun secteur disponible</flux:callout.heading>
            <flux:callout.text>Cette catégorie n'a pas encore de secteurs actifs.</flux:callout.text>
        </flux:callout>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($sectors as $sector)
                <a
                    href="{{ route('compare.products', [$category->slug, $sector->slug]) }}"
                    wire:navigate
                    class="group rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 shadow-sm hover:shadow-md hover:border-zinc-400 dark:hover:border-zinc-500 transition-all"
                >
                    <div class="mb-3 flex items-start justify-between">
                        <flux:heading size="lg" class="group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            {{ $sector->name }}
                        </flux:heading>
                        <flux:badge color="zinc" size="sm">{{ $sector->products_count }} produit{{ $sector->products_count > 1 ? 's' : '' }}</flux:badge>
                    </div>

                    @if ($sector->description)
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2">
                            {{ $sector->description }}
                        </flux:text>
                    @endif

                    <div class="mt-4 flex items-center gap-1 text-sm font-medium text-blue-600 dark:text-blue-400">
                        Voir les produits
                        <flux:icon.arrow-right class="size-4" />
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
