<?php

use App\Models\Category;
use Livewire\Component;

new class extends Component
{
    /** @var \Illuminate\Database\Eloquent\Collection<int, Category> */
    public $categories;

    public function mount(): void
    {
        $this->categories = Category::query()
            ->where('is_active', true)
            ->withCount(['sectors' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();
    }

    public function render()
    {
        return $this->view()->layout('layouts.compare')->title('Choisir une catégorie — ' . config('app.name'));
    }
};
?>

<div>
    <div class="mb-8 text-center">
        <flux:heading size="2xl" class="mb-2">Que souhaitez-vous comparer ?</flux:heading>
        <flux:text class="text-zinc-500 dark:text-zinc-400">
            Sélectionnez une catégorie pour démarrer votre comparaison gratuite.
        </flux:text>
    </div>

    @if ($categories->isEmpty())
        <flux:callout icon="information-circle">
            <flux:callout.heading>Aucune catégorie disponible</flux:callout.heading>
            <flux:callout.text>Revenez bientôt, de nouvelles catégories arrivent prochainement.</flux:callout.text>
        </flux:callout>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <a
                    href="{{ route('compare.sectors', $category->slug) }}"
                    wire:navigate
                    class="group rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 shadow-sm hover:shadow-md hover:border-zinc-400 dark:hover:border-zinc-500 transition-all"
                >
                    <div class="mb-3 flex items-start justify-between">
                        <flux:heading size="lg" class="group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            {{ $category->name }}
                        </flux:heading>
                        <flux:badge color="zinc" size="sm">{{ $category->sectors_count }} secteur{{ $category->sectors_count > 1 ? 's' : '' }}</flux:badge>
                    </div>

                    @if ($category->description)
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2">
                            {{ $category->description }}
                        </flux:text>
                    @endif

                    <div class="mt-4 flex items-center gap-1 text-sm font-medium text-blue-600 dark:text-blue-400">
                        Comparer
                        <flux:icon.arrow-right class="size-4" />
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
