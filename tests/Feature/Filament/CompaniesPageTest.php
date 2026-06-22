<?php

use App\Enums\TeamRole;
use App\Filament\Pages\CompaniesPage;
use App\Models\Category;
use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use App\Notifications\Companies\CompanyAssignedManager;
use App\Notifications\Companies\CompanyManagerCredentials;
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

test('admin can create a company and provision a new manager account', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Assurance', 'slug' => 'assurance']);
    $team = Team::factory()->create(['name' => 'Allianz Team', 'slug' => 'allianz-team']);

    $this->actingAs($admin);

    Livewire::test(CompaniesPage::class)
        ->call('mountAction', 'create')
        ->set([
            'mountedActions.0.data.manager_name' => 'Claire Mukendi',
            'mountedActions.0.data.manager_email' => 'claire.mukendi@allianz.example.com',
            'mountedActions.0.data.category_id' => $category->id,
            'mountedActions.0.data.team_id' => $team->id,
            'mountedActions.0.data.name' => 'Allianz Congo',
            'mountedActions.0.data.slug' => 'allianz-congo',
            'mountedActions.0.data.logo_path' => 'logos/allianz-congo.png',
            'mountedActions.0.data.description' => 'Entreprise partenaire du secteur assurance.',
            'mountedActions.0.data.website_url' => 'https://allianz.example.com',
            'mountedActions.0.data.support_email' => 'support@allianz.example.com',
            'mountedActions.0.data.support_phone' => '+243 970 000 000',
            'mountedActions.0.data.contact_name' => 'Service commercial',
            'mountedActions.0.data.address_line_1' => '10 avenue de la Paix',
            'mountedActions.0.data.address_line_2' => 'Immeuble A',
            'mountedActions.0.data.city' => 'Lubumbashi',
            'mountedActions.0.data.postal_code' => '7001',
            'mountedActions.0.data.country' => 'RDC',
            'mountedActions.0.data.is_active' => true,
        ])
        ->call('callMountedAction')
        ->assertHasNoFormErrors();

    $manager = User::where('email', 'claire.mukendi@allianz.example.com')->firstOrFail();

    $this->assertDatabaseHas(Company::class, [
        'category_id' => $category->id,
        'team_id' => $team->id,
        'manager_id' => $manager->id,
        'name' => 'Allianz Congo',
        'slug' => 'allianz-congo',
        'support_email' => 'support@allianz.example.com',
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('team_members', [
        'team_id' => $team->id,
        'user_id' => $manager->id,
        'role' => TeamRole::Owner->value,
    ]);

    Notification::assertSentTo($manager, CompanyManagerCredentials::class,
        fn (CompanyManagerCredentials $n): bool => $n->temporaryPassword !== '' && $n->company->name === 'Allianz Congo'
    );
});

test('manager email must be unique when creating a company', function () {
    $admin = User::factory()->admin()->create();
    $existingUser = User::factory()->create(['email' => 'already@taken.com']);
    $category = Category::factory()->create();
    $team = Team::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CompaniesPage::class)
        ->call('mountAction', 'create')
        ->set([
            'mountedActions.0.data.manager_name' => 'Jean Dupont',
            'mountedActions.0.data.manager_email' => 'already@taken.com',
            'mountedActions.0.data.category_id' => $category->id,
            'mountedActions.0.data.team_id' => $team->id,
            'mountedActions.0.data.name' => 'Test Corp',
            'mountedActions.0.data.slug' => 'test-corp',
            'mountedActions.0.data.is_active' => true,
        ])
        ->call('callMountedAction')
        ->assertHasFormErrors(['manager_email']);

    $this->assertDatabaseMissing(Company::class, ['slug' => 'test-corp']);
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
        ->mountTableAction('edit', $company)
        ->set([
            'mountedActions.0.data.category_id' => $updatedCategory->id,
            'mountedActions.0.data.team_id' => $updatedTeam->id,
            'mountedActions.0.data.manager_id' => $updatedOwner->id,
            'mountedActions.0.data.name' => 'Rawsur Premium',
            'mountedActions.0.data.slug' => 'rawsur-premium',
            'mountedActions.0.data.logo_path' => 'logos/rawsur-premium.png',
            'mountedActions.0.data.description' => 'Fiche partenaire mise a jour.',
            'mountedActions.0.data.website_url' => 'https://premium.rawsur.test',
            'mountedActions.0.data.support_email' => 'support@premium.rawsur.test',
            'mountedActions.0.data.support_phone' => '+243 999 000 111',
            'mountedActions.0.data.contact_name' => 'Paul Ilunga',
            'mountedActions.0.data.address_line_1' => '25 boulevard Kasa-Vubu',
            'mountedActions.0.data.address_line_2' => null,
            'mountedActions.0.data.city' => 'Kinshasa',
            'mountedActions.0.data.postal_code' => '1000',
            'mountedActions.0.data.country' => 'RDC',
            'mountedActions.0.data.is_active' => false,
        ])
        ->callMountedTableAction()
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

test('CompanyManagerCredentials notification contains the temporary password and the company name', function () {
    $company = Company::factory()->create(['name' => 'AXA Congo']);
    $manager = User::factory()->create();
    $notification = new CompanyManagerCredentials($company, 'S3cr3tPass!');

    $mail = $notification->toMail($manager);

    expect($mail->subject)->toContain('AXA Congo')
        ->and(implode(' ', $mail->introLines))->toContain('S3cr3tPass!')
        ->and($mail->actionUrl)->toBe(route('login'));
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
        ->mountTableAction('edit', $company)
        ->set([
            'mountedActions.0.data.category_id' => $category->id,
            'mountedActions.0.data.team_id' => $team->id,
            'mountedActions.0.data.manager_id' => $newOwner->id,
            'mountedActions.0.data.name' => $company->name,
            'mountedActions.0.data.slug' => $company->slug,
            'mountedActions.0.data.is_active' => true,
        ])
        ->callMountedTableAction()
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
        ->mountTableAction('edit', $company)
        ->set([
            'mountedActions.0.data.category_id' => $category->id,
            'mountedActions.0.data.team_id' => $team->id,
            'mountedActions.0.data.manager_id' => $owner->id,
            'mountedActions.0.data.name' => 'Updated Name',
            'mountedActions.0.data.slug' => 'updated-name',
            'mountedActions.0.data.is_active' => true,
        ])
        ->callMountedTableAction()
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
