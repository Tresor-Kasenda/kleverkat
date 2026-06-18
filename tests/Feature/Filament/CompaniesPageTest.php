<?php

use App\Enums\TeamRole;
use App\Filament\Pages\CompaniesPage;
use App\Models\Category;
use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use App\Notifications\Companies\CompanyAssignedManager;
use Illuminate\Support\Facades\Notification;
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

test('admin can link a company to a category, a team and its owner', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Assurance', 'slug' => 'assurance']);
    $owner = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Allianz Team', 'slug' => 'allianz-team']);
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $this->actingAs($admin);

    Livewire::test(CompaniesPage::class)
        ->callAction('create', data: [
            'category_id' => $category->id,
            'team_id' => $team->id,
            'manager_id' => $owner->id,
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
        'category_id' => $category->id,
        'team_id' => $team->id,
        'manager_id' => $owner->id,
        'name' => 'Allianz Congo',
        'slug' => 'allianz-congo',
        'support_email' => 'support@allianz.example.com',
        'contact_name' => 'Claire Mukendi',
        'is_active' => true,
    ]);
});

test('the manager must be an owner of the selected team', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($admin);

    Livewire::test(CompaniesPage::class)
        ->callAction('create', data: [
            'category_id' => $category->id,
            'team_id' => $team->id,
            'manager_id' => $member->id,
            'name' => 'Rawsur Congo',
            'slug' => 'rawsur-congo',
            'is_active' => true,
        ])
        ->assertHasFormErrors(['manager_id']);

    $this->assertDatabaseMissing(Company::class, ['slug' => 'rawsur-congo']);
});

test('admin can edit a company from companies page', function () {
    $admin = User::factory()->admin()->create();
    $initialCategory = Category::factory()->create();
    $updatedCategory = Category::factory()->create();
    $initialOwner = User::factory()->create();
    $updatedOwner = User::factory()->create();
    $initialTeam = Team::factory()->create();
    $updatedTeam = Team::factory()->create();
    $initialTeam->members()->attach($initialOwner, ['role' => TeamRole::Owner->value]);
    $updatedTeam->members()->attach($updatedOwner, ['role' => TeamRole::Owner->value]);
    $company = Company::factory()->create([
        'category_id' => $initialCategory->id,
        'team_id' => $initialTeam->id,
        'manager_id' => $initialOwner->id,
        'name' => 'Rawsur Test',
        'slug' => 'rawsur-test',
        'support_email' => 'contact@rawsur.test',
        'website_url' => 'https://rawsur.test',
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(CompaniesPage::class)
        ->callTableAction('edit', $company, [
            'category_id' => $updatedCategory->id,
            'team_id' => $updatedTeam->id,
            'manager_id' => $updatedOwner->id,
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
        'category_id' => $updatedCategory->id,
        'team_id' => $updatedTeam->id,
        'manager_id' => $updatedOwner->id,
        'name' => 'Rawsur Premium',
        'slug' => 'rawsur-premium',
        'support_email' => 'support@premium.rawsur.test',
        'city' => 'Kinshasa',
        'is_active' => false,
    ]);
});

test('notification is sent to manager when company is created', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $this->actingAs($admin);

    Livewire::test(CompaniesPage::class)
        ->callAction('create', data: [
            'category_id' => $category->id,
            'team_id' => $team->id,
            'manager_id' => $owner->id,
            'name' => 'Allianz Congo',
            'slug' => 'allianz-congo',
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    Notification::assertSentTo($owner, CompanyAssignedManager::class, function (CompanyAssignedManager $notification): bool {
        return $notification->isNew === true
            && $notification->company->name === 'Allianz Congo';
    });
});

test('notification is sent to new manager when manager changes during edit', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $oldOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach([$oldOwner->id, $newOwner->id], ['role' => TeamRole::Owner->value]);
    $company = Company::factory()->create([
        'category_id' => $category->id,
        'team_id' => $team->id,
        'manager_id' => $oldOwner->id,
    ]);

    $this->actingAs($admin);

    Livewire::test(CompaniesPage::class)
        ->callTableAction('edit', $company, [
            'category_id' => $category->id,
            'team_id' => $team->id,
            'manager_id' => $newOwner->id,
            'name' => $company->name,
            'slug' => $company->slug,
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    Notification::assertSentTo($newOwner, CompanyAssignedManager::class, function (CompanyAssignedManager $notification): bool {
        return $notification->isNew === false;
    });
});

test('no notification is sent when manager does not change during edit', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $company = Company::factory()->create([
        'category_id' => $category->id,
        'team_id' => $team->id,
        'manager_id' => $owner->id,
    ]);

    $this->actingAs($admin);

    Livewire::test(CompaniesPage::class)
        ->callTableAction('edit', $company, [
            'category_id' => $category->id,
            'team_id' => $team->id,
            'manager_id' => $owner->id,
            'name' => 'Updated Name',
            'slug' => 'updated-name',
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    Notification::assertNotSentTo($owner, CompanyAssignedManager::class);
});

test('admin can delete a company from companies page', function () {
    $admin = User::factory()->admin()->create();
    $company = Company::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CompaniesPage::class)
        ->callTableAction('delete', $company);

    $this->assertDatabaseMissing(Company::class, [
        'id' => $company->id,
    ]);
});
