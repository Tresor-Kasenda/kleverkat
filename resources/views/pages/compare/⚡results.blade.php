<?php

use App\Actions\Leads\CreateLead;
use App\Enums\LeadActionType;
use App\Models\ComparisonResult;
use App\Models\ComparisonSession;
use App\Services\Comparison\ComparisonService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    public ComparisonSession $session;

    /** @var Collection<int, ComparisonResult> */
    public Collection $results;

    public bool $showModal = false;

    public ?int $selectedResultId = null;

    public string $actionType = '';

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $phone = '';

    public bool $leadSent = false;

    public function mount(ComparisonSession $session): void
    {
        $this->session = $session->load('product.sector.category');

        if (! $session->isCompleted()) {
            abort(404);
        }

        $this->results = app(ComparisonService::class)->rankedByPrice($session);
    }

    public function openModal(int $resultId, string $actionType): void
    {
        $this->reset('firstName', 'lastName', 'email', 'phone', 'leadSent');
        $this->selectedResultId = $resultId;
        $this->actionType = $actionType;
        $this->showModal = true;
    }

    public function submitLead(): void
    {
        $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $result = $this->results->firstWhere('id', $this->selectedResultId);

        if ($result === null) {
            return;
        }

        app(CreateLead::class)->handle(
            $result,
            [
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'email' => $this->email,
                'phone' => $this->phone ?: null,
            ],
            LeadActionType::from($this->actionType),
        );

        $this->leadSent = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset('selectedResultId', 'actionType', 'firstName', 'lastName', 'email', 'phone', 'leadSent');
    }

    public function render()
    {
        return $this->view()
            ->layout('layouts.compare')
            ->title('Résultats — ' . $this->session->product->name . ' — ' . config('app.name'));
    }
};
?>

<div>
    @php
        $product  = $session->product;
        $sector   = $product->sector;
        $category = $sector->category;
    @endphp

    <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
        <a href="{{ route('compare.categories') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">Catégories</a>
        <flux:icon.chevron-right class="size-4" />
        <a href="{{ route('compare.sectors', $category->slug) }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">{{ $category->name }}</a>
        <flux:icon.chevron-right class="size-4" />
        <a href="{{ route('compare.products', [$category->slug, $sector->slug]) }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">{{ $sector->name }}</a>
        <flux:icon.chevron-right class="size-4" />
        <span class="text-zinc-900 dark:text-zinc-100 font-medium">Résultats</span>
    </nav>

    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="2xl" class="mb-1">{{ $product->name }}</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                {{ $results->count() }} offre{{ $results->count() > 1 ? 's' : '' }} trouvée{{ $results->count() > 1 ? 's' : '' }}, classée{{ $results->count() > 1 ? 's' : '' }} du meilleur prix
            </flux:text>
        </div>
        <flux:button
            href="{{ route('compare.wizard', [$category->slug, $sector->slug, $product->slug]) }}"
            wire:navigate
            variant="ghost"
            icon="arrow-left"
            size="sm"
        >
            Modifier mes réponses
        </flux:button>
    </div>

    @if ($results->isEmpty())
        <flux:callout icon="exclamation-circle" variant="warning">
            <flux:callout.heading>Aucune offre éligible</flux:callout.heading>
            <flux:callout.text>
                Aucune offre ne correspond à votre profil. Essayez de modifier vos réponses au questionnaire.
            </flux:callout.text>
        </flux:callout>
    @else
        <div class="space-y-4">
            @foreach ($results as $index => $result)
                <div
                    wire:key="result-{{ $result->id }}"
                    class="rounded-2xl border bg-white dark:bg-zinc-900 p-6 shadow-sm
                        {{ $index === 0 ? 'border-blue-300 dark:border-blue-700 ring-1 ring-blue-200 dark:ring-blue-800' : 'border-zinc-200 dark:border-zinc-700' }}"
                >
                    @if ($index === 0)
                        <div class="mb-3">
                            <flux:badge color="blue" size="sm" icon="star">Meilleur prix</flux:badge>
                        </div>
                    @endif

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <flux:heading size="lg">{{ $result->offer->company->name }}</flux:heading>
                                @if ($result->offer->company->is_active)
                                    <flux:badge color="green" size="sm">Partenaire actif</flux:badge>
                                @endif
                            </div>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mb-3">
                                {{ $result->offer->name }}
                            </flux:text>

                            @if ($result->offer->features->isNotEmpty())
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($result->offer->features->take(5) as $feature)
                                        <flux:badge color="zinc" size="sm" icon="check">{{ $feature->name }}</flux:badge>
                                    @endforeach
                                    @if ($result->offer->features->count() > 5)
                                        <flux:badge color="zinc" size="sm">+{{ $result->offer->features->count() - 5 }}</flux:badge>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col items-end gap-3 shrink-0">
                            @if ($result->calculated_price !== null)
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ number_format((float) $result->calculated_price, 2, ',', ' ') }} €
                                    </div>
                                    <flux:text class="text-xs text-zinc-400">/ mois</flux:text>
                                </div>
                            @else
                                <div class="text-right">
                                    <flux:badge color="amber">Sur devis</flux:badge>
                                </div>
                            @endif

                            <div class="flex flex-col gap-2 w-full sm:w-auto sm:min-w-40">
                                <flux:button
                                    wire:click="openModal({{ $result->id }}, '{{ LeadActionType::QuoteRequest->value }}')"
                                    variant="primary"
                                    size="sm"
                                    class="w-full"
                                >
                                    Demande de devis
                                </flux:button>

                                <flux:button
                                    wire:click="openModal({{ $result->id }}, '{{ LeadActionType::Callback->value }}')"
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                >
                                    Être rappelé(e)
                                </flux:button>

                                @if ($result->offer->company->website_url)
                                    <flux:button
                                        href="{{ $result->offer->company->website_url }}"
                                        target="_blank"
                                        variant="ghost"
                                        size="sm"
                                        icon-trailing="arrow-top-right-on-square"
                                        class="w-full"
                                        wire:click="openModal({{ $result->id }}, '{{ LeadActionType::PartnerRedirect->value }}')"
                                    >
                                        Voir l'offre
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Lead contact modal --}}
    <flux:modal wire:model="showModal" class="max-w-md">
        @if ($leadSent)
            <div class="text-center py-4">
                <flux:icon.check-circle class="size-12 text-green-500 mx-auto mb-4" />
                <flux:heading size="lg" class="mb-2">Demande envoyée !</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400 mb-6">
                    Votre demande a bien été transmise. Un conseiller vous contactera rapidement.
                </flux:text>
                <flux:button wire:click="closeModal" variant="primary">Fermer</flux:button>
            </div>
        @else
            <flux:heading size="lg" class="mb-1">
                @if ($actionType === \App\Enums\LeadActionType::QuoteRequest->value)
                    Demande de devis
                @elseif ($actionType === \App\Enums\LeadActionType::Callback->value)
                    Demande de rappel
                @else
                    Vos coordonnées
                @endif
            </flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400 mb-6">
                Laissez vos coordonnées pour que le partenaire puisse vous contacter.
            </flux:text>

            <form wire:submit="submitLead" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model="firstName"
                        label="Prénom"
                        required
                        autofocus
                    />
                    <flux:input
                        wire:model="lastName"
                        label="Nom"
                        required
                    />
                </div>
                <flux:input
                    wire:model="email"
                    type="email"
                    label="Email"
                    required
                />
                <flux:input
                    wire:model="phone"
                    type="tel"
                    label="Téléphone (optionnel)"
                />

                <div class="flex gap-3 pt-2">
                    <flux:button type="button" wire:click="closeModal" variant="ghost" class="flex-1">
                        Annuler
                    </flux:button>
                    <flux:button type="submit" variant="primary" class="flex-1">
                        Envoyer ma demande
                    </flux:button>
                </div>
            </form>
        @endif
    </flux:modal>
</div>
