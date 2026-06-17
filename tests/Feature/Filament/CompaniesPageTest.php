<?php

use App\Enums\TeamRole;
use App\Filament\Pages\CompaniesPage;
use App\Models\Company;
use App\Models\Sector;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

test('admin can access companies page and see companies', function () {
    $admin = User::factory()->admin()->create();
    $companies = Company::factory()->count(2)->create();

    $this->actingAs($admin);

    $this->get(CompaniesPage::getUrl())->assertSuccessful();
    Livewire::test(CompaniesPage::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($companies);
});

test('non admin can not access companies page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(CompaniesPage::getUrl())
        ->assertForbidden();
});

test('admin can create a company from companies page', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->create();
    $team = Team::factory()->create([
        'name' => 'Allianz Team',
        'slug' => 'allianz-team',
    ]);
    $sector = Sector::factory()->create([
        'name' => 'Assurance',
        'slug' => 'assurance',
    ]);
    $team->members()->attach($manager, ['role' => TeamRole::Member->value]);

    $this->actingAs($admin);

    Livewire::test(CompaniesPage::class)
        ->callAction('create', data: [
            'sector_id' => $sector->id,
            'team_id' => $team->id,
            'manager_id' => $manager->id,
            'name' => 'Allianz Congo',
            'slug' => 'allianz-congo',
            'logo_path' => 'logos/allianz-congo.png',
            'description' => 'Entreprise partenaire du secteur assurance.',
            'website_url' => 'https://allianz.example.com',
            'support_email' => 'support@allianz.example.com',
            'support_phone' => '+243 970 000 000',
            'contact_name' => 'Claire Mukendi',
            'address_line_1' => '10 avenue de la Paix',
            'address_line_2' => 'Immeuble A',
            'city' => 'Lubumbashi',
            'postal_code' => '7001',
            'country' => 'RDC',
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Company::class, [
        'sector_id' => $sector->id,
        'team_id' => $team->id,
        'manager_id' => $manager->id,
        'name' => 'Allianz Congo',
        'slug' => 'allianz-congo',
        'support_email' => 'support@allianz.example.com',
        'contact_name' => 'Claire Mukendi',
        'is_active' => true,
    ]);
});

test('admin can edit a company from companies page', function () {
    $admin = User::factory()->admin()->create();
    $initialSector = Sector::factory()->create();
    $updatedSector = Sector::factory()->create();
    $initialManager = User::factory()->create();
    $updatedManager = User::factory()->create();
    $initialTeam = Team::factory()->create();
    $updatedTeam = Team::factory()->create();
    $initialTeam->members()->attach($initialManager, ['role' => TeamRole::Member->value]);
    $updatedTeam->members()->attach($updatedManager, ['role' => TeamRole::Admin->value]);
    $company = Company::factory()->create([
        'sector_id' => $initialSector->id,
        'team_id' => $initialTeam->id,
        'manager_id' => $initialManager->id,
        'name' => 'Rawsur Test',
        'slug' => 'rawsur-test',
        'support_email' => 'contact@rawsur.test',
        'website_url' => 'https://rawsur.test',
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(CompaniesPage::class)
        ->callAction('edit', $company, [
            'sector_id' => $updatedSector->id,
            'team_id' => $updatedTeam->id,
            'manager_id' => $updatedManager->id,
            'name' => 'Rawsur Premium',
            'slug' => 'rawsur-premium',
            'logo_path' => 'logos/rawsur-premium.png',
            'description' => 'Fiche partenaire mise a jour.',
            'website_url' => 'https://premium.rawsur.test',
            'support_email' => 'support@premium.rawsur.test',
            'support_phone' => '+243 999 000 111',
            'contact_name' => 'Paul Ilunga',
            'address_line_1' => '25 boulevard Kasa-Vubu',
            'address_line_2' => null,
            'city' => 'Kinshasa',
            'postal_code' => '1000',
            'country' => 'RDC',
            'is_active' => false,
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Company::class, [
        'id' => $company->id,
        'sector_id' => $updatedSector->id,
        'team_id' => $updatedTeam->id,
        'manager_id' => $updatedManager->id,
        'name' => 'Rawsur Premium',
        'slug' => 'rawsur-premium',
        'support_email' => 'support@premium.rawsur.test',
        'city' => 'Kinshasa',
        'is_active' => false,
    ]);
});

test('admin can delete a company from companies page', function () {
    $admin = User::factory()->admin()->create();
    $company = Company::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CompaniesPage::class)
        ->callAction('delete', $company);

    $this->assertDatabaseMissing(Company::class, [
        'id' => $company->id,
    ]);
});
