<?php

use App\Models\Company;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{
    public Company $company;

    public bool $canUpdate = false;

    public ?string $logoPath = null;

    public ?string $description = null;

    public ?string $websiteUrl = null;

    public ?string $supportEmail = null;

    public ?string $supportPhone = null;

    public ?string $contactName = null;

    public ?string $addressLine1 = null;

    public ?string $addressLine2 = null;

    public ?string $city = null;

    public ?string $postalCode = null;

    public ?string $country = null;

    public function mount(): void
    {
        $team = Auth::user()?->currentTeam()->first();

        abort_if($team === null, 404);

        $company = $team->company()
            ->with(['manager', 'category', 'team'])
            ->first();

        abort_if($company === null, 404);

        Gate::authorize('view', $company);

        $this->company = $company;
        $this->canUpdate = Gate::allows('update', $company);

        $this->fillForm();
    }

    public function save(): void
    {
        Gate::authorize('update', $this->company);

        $validated = $this->validate($this->rules());

        $this->company->update([
            'logo_path' => $validated['logoPath'] ?: null,
            'description' => $validated['description'] ?: null,
            'website_url' => $validated['websiteUrl'] ?: null,
            'support_email' => $validated['supportEmail'] ?: null,
            'support_phone' => $validated['supportPhone'] ?: null,
            'contact_name' => $validated['contactName'] ?: null,
            'address_line_1' => $validated['addressLine1'] ?: null,
            'address_line_2' => $validated['addressLine2'] ?: null,
            'city' => $validated['city'] ?: null,
            'postal_code' => $validated['postalCode'] ?: null,
            'country' => $validated['country'] ?: null,
        ]);

        $this->company = $this->company->fresh(['manager', 'category', 'team']);

        $this->fillForm();

        Flux::toast(variant: 'success', text: __('Profil entreprise mis à jour.'));
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'logoPath' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'websiteUrl' => ['nullable', 'url', 'max:255'],
            'supportEmail' => ['nullable', 'email', 'max:255'],
            'supportPhone' => ['nullable', 'string', 'max:255'],
            'contactName' => ['nullable', 'string', 'max:255'],
            'addressLine1' => ['nullable', 'string', 'max:255'],
            'addressLine2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postalCode' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function missingFields(): array
    {
        return $this->company->missingProfileFields();
    }

    public function render()
    {
        return $this->view()->title(__('Mon entreprise'));
    }

    protected function fillForm(): void
    {
        $this->logoPath = $this->company->logo_path;
        $this->description = $this->company->description;
        $this->websiteUrl = $this->company->website_url;
        $this->supportEmail = $this->company->support_email;
        $this->supportPhone = $this->company->support_phone;
        $this->contactName = $this->company->contact_name;
        $this->addressLine1 = $this->company->address_line_1;
        $this->addressLine2 = $this->company->address_line_2;
        $this->city = $this->company->city;
        $this->postalCode = $this->company->postal_code;
        $this->country = $this->company->country;
    }
};
?>

<section class="w-full">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2">
                    <flux:heading size="xl">{{ $company->name }}</flux:heading>
                    <flux:text class="max-w-3xl text-sm text-zinc-500 dark:text-zinc-400">
                        Complète le profil de l’entreprise pour que le catalogue partenaire soit exploitable côté admin et prêt pour l’affichage public.
                    </flux:text>
                </div>

                @if ($company->isProfileComplete())
                    <flux:badge color="green">Profil complet</flux:badge>
                @else
                    <flux:badge color="amber">Profil à compléter</flux:badge>
                @endif
            </div>
        </div>

        @if (count($this->missingFields()) > 0)
            <flux:callout icon="exclamation-circle" variant="warning">
                <flux:callout.heading>Informations encore manquantes</flux:callout.heading>
                <flux:callout.text>
                    Les champs suivants devraient être complétés avant l’activation publique du profil.
                </flux:callout.text>

                <div class="flex flex-wrap gap-2">
                    @foreach ($this->missingFields() as $field)
                        <flux:badge wire:key="missing-field-{{ md5($field) }}" color="amber">{{ $field }}</flux:badge>
                    @endforeach
                </div>
            </flux:callout>
        @endif

        @if (! $canUpdate)
            <flux:callout icon="lock-closed" color="zinc">
                <flux:callout.heading>Accès en lecture seule</flux:callout.heading>
                <flux:callout.text>
                    Seul le gestionnaire désigné ou un responsable d’équipe peut modifier ce profil.
                </flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-6 xl:grid-cols-[20rem_minmax(0,1fr)]">
            <aside class="space-y-6">
                <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="lg">Référentiel</flux:heading>

                    <dl class="mt-6 space-y-4">
                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500">Catégorie</dt>
                            <dd class="text-sm text-zinc-800 dark:text-zinc-200">{{ $company->category->name }}</dd>
                        </div>

                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500">Équipe</dt>
                            <dd class="text-sm text-zinc-800 dark:text-zinc-200">{{ $company->team?->name ?? 'Non liée' }}</dd>
                        </div>

                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500">Gestionnaire</dt>
                            <dd class="text-sm text-zinc-800 dark:text-zinc-200">{{ $company->manager?->name ?? 'Non assigné' }}</dd>
                        </div>

                        <div class="space-y-1">
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500">Statut</dt>
                            <dd class="text-sm text-zinc-800 dark:text-zinc-200">{{ $company->is_active ? 'Actif' : 'Inactif' }}</dd>
                        </div>
                    </dl>
                </div>
            </aside>

            <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-6 space-y-2">
                    <flux:heading size="lg">Profil entreprise</flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        Mets à jour les coordonnées, la présentation et les informations de contact utilisées par l’équipe et, plus tard, par la fiche publique.
                    </flux:text>
                </div>

                <form wire:submit="save" class="space-y-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:input
                            wire:model="logoPath"
                            :label="__('Chemin du logo')"
                            :disabled="! $canUpdate"
                        />

                        <flux:input
                            wire:model="websiteUrl"
                            :label="__('Site web')"
                            type="url"
                            :disabled="! $canUpdate"
                        />
                    </div>

                    <flux:textarea
                        wire:model="description"
                        :label="__('Description')"
                        rows="6"
                        :disabled="! $canUpdate"
                    />

                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:input
                            wire:model="supportEmail"
                            :label="__('Email support')"
                            type="email"
                            :disabled="! $canUpdate"
                        />

                        <flux:input
                            wire:model="supportPhone"
                            :label="__('Téléphone support')"
                            :disabled="! $canUpdate"
                        />

                        <flux:input
                            wire:model="contactName"
                            :label="__('Contact principal')"
                            :disabled="! $canUpdate"
                        />

                        <flux:input
                            wire:model="country"
                            :label="__('Pays')"
                            :disabled="! $canUpdate"
                        />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:input
                            wire:model="addressLine1"
                            :label="__('Adresse')"
                            :disabled="! $canUpdate"
                        />

                        <flux:input
                            wire:model="addressLine2"
                            :label="__('Complément d’adresse')"
                            :disabled="! $canUpdate"
                        />

                        <flux:input
                            wire:model="city"
                            :label="__('Ville')"
                            :disabled="! $canUpdate"
                        />

                        <flux:input
                            wire:model="postalCode"
                            :label="__('Code postal')"
                            :disabled="! $canUpdate"
                        />
                    </div>

                    @if ($canUpdate)
                        <div class="flex justify-end">
                            <flux:button variant="primary" type="submit" data-test="company-profile-save-button">
                                Enregistrer le profil
                            </flux:button>
                        </div>
                    @endif
                </form>
            </section>
        </div>
    </div>
</section>
