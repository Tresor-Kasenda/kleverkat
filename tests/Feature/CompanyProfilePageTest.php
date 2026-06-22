<?php

use App\Enums\TeamRole;
use App\Models\Category;
use App\Models\Company;
use App\Models\Team;
use App\Models\User;

test('company manager can access the company profile page', function () {
    $manager = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($manager, ['role' => TeamRole::Member->value]);
    $manager->switchTeam($team);

    Company::factory()->create([
        'category_id' => Category::factory()->create()->id,
        'team_id' => $team->id,
        'manager_id' => $manager->id,
        'name' => 'Sunu Assurances',
    ]);

    $this->actingAs($manager)
        ->get(route('company.profile', ['current_team' => $team->slug]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Companies/Profile'));
});

test('company manager can update missing company information', function () {
    $manager = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($manager, ['role' => TeamRole::Member->value]);
    $manager->switchTeam($team);

    $company = Company::factory()->create([
        'category_id' => Category::factory()->create()->id,
        'team_id' => $team->id,
        'manager_id' => $manager->id,
        'description' => null,
        'website_url' => null,
        'support_email' => null,
        'support_phone' => null,
        'contact_name' => null,
        'address_line_1' => null,
        'city' => null,
        'country' => null,
    ]);

    $this->actingAs($manager)
        ->put(route('company.profile.update', ['current_team' => $team->slug]), [
            'description' => 'Assureur spécialisé pour le marché local.',
            'website_url' => 'https://sunu.example.com',
            'support_email' => 'support@sunu.example.com',
            'support_phone' => '+243 810 000 000',
            'contact_name' => 'Nadine Mwamba',
            'address_line_1' => '15 avenue Lumumba',
            'city' => 'Lubumbashi',
            'postal_code' => '7001',
            'country' => 'RDC',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas(Company::class, [
        'id' => $company->id,
        'description' => 'Assureur spécialisé pour le marché local.',
        'website_url' => 'https://sunu.example.com',
        'support_email' => 'support@sunu.example.com',
        'contact_name' => 'Nadine Mwamba',
        'city' => 'Lubumbashi',
        'country' => 'RDC',
    ]);
});

test('company members can view the page but cannot update it if they are not managers', function () {
    $member = User::factory()->create();
    $manager = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($manager, ['role' => TeamRole::Member->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);
    $member->switchTeam($team);

    Company::factory()->create([
        'category_id' => Category::factory()->create()->id,
        'team_id' => $team->id,
        'manager_id' => $manager->id,
        'name' => 'Equity Banque',
    ]);

    $this->actingAs($member)
        ->get(route('company.profile', ['current_team' => $team->slug]))
        ->assertOk();

    $this->actingAs($member)
        ->put(route('company.profile.update', ['current_team' => $team->slug]), [
            'support_email' => 'member@equity.example.com',
        ])
        ->assertForbidden();
});

test('company profile page returns 404 when the current team has no linked company', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);
    $user->switchTeam($team);

    $this->actingAs($user)
        ->get(route('company.profile', ['current_team' => $team->slug]))
        ->assertNotFound();
});
