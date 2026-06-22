<?php

namespace App\Http\Controllers\Companies;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CompanyProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $team = $request->user()?->currentTeam()->first();

        abort_if($team === null, 404);

        $company = $team->company()
            ->with(['manager', 'category', 'team'])
            ->first();

        abort_if($company === null, 404);

        Gate::authorize('view', $company);

        return Inertia::render('Companies/Profile', [
            'company'   => $this->serializeCompany($company),
            'canUpdate' => Gate::allows('update', $company),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $team = $request->user()?->currentTeam()->first();
        abort_if($team === null, 404);

        $company = $team->company()->first();
        abort_if($company === null, 404);

        Gate::authorize('update', $company);

        $validated = $request->validate([
            'description'   => ['nullable', 'string', 'max:2000'],
            'website_url'   => ['nullable', 'url', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'contact_name'  => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:100'],
            'postal_code'   => ['nullable', 'string', 'max:20'],
            'country'       => ['nullable', 'string', 'max:100'],
        ]);

        $company->update($validated);

        return back()->with('success', 'Profil entreprise mis à jour.');
    }

    /** @return array<string, mixed> */
    private function serializeCompany(\App\Models\Company $company): array
    {
        return [
            'id'            => $company->id,
            'name'          => $company->name,
            'description'   => $company->description,
            'website_url'   => $company->website_url,
            'support_email' => $company->support_email,
            'support_phone' => $company->support_phone,
            'contact_name'  => $company->contact_name,
            'address_line_1' => $company->address_line_1,
            'address_line_2' => $company->address_line_2,
            'city'          => $company->city,
            'postal_code'   => $company->postal_code,
            'country'       => $company->country,
            'manager'       => $company->manager ? [
                'id'    => $company->manager->id,
                'name'  => $company->manager->name,
                'email' => $company->manager->email,
            ] : null,
            'category' => $company->category ? [
                'name' => $company->category->name,
            ] : null,
        ];
    }
}
