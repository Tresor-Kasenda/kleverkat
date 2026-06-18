<?php

use App\Enums\TeamRole;
use App\Filament\Pages\TeamsPage;
use App\Models\Team;
use App\Models\User;
use App\Notifications\Teams\TeamMemberCredentials;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('admin can access teams page and see partner teams', function () {
    $admin = User::factory()->admin()->create();
    $teams = Team::factory()->count(2)->create();

    $this->actingAs($admin);

    $this->get(TeamsPage::getUrl())->assertSuccessful();
    Livewire::test(TeamsPage::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($teams);
});

test('non admin can not access teams page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(TeamsPage::getUrl())
        ->assertForbidden();
});

test('admin can create a team and provision its members', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(TeamsPage::class)
        ->callAction('create', data: [
            'name' => 'Allianz Team',
            'slug' => 'allianz-team',
            'members' => [
                ['name' => 'Claire Mukendi', 'email' => 'claire@allianz.example.com', 'role' => TeamRole::Owner->value],
                ['name' => 'Paul Ilunga', 'email' => 'paul@allianz.example.com', 'role' => TeamRole::Member->value],
            ],
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('teams', [
        'slug' => 'allianz-team',
        'is_personal' => false,
    ]);

    $team = Team::query()->where('slug', 'allianz-team')->firstOrFail();
    $owner = User::query()->where('email', 'claire@allianz.example.com')->firstOrFail();
    $member = User::query()->where('email', 'paul@allianz.example.com')->firstOrFail();

    $this->assertDatabaseHas('team_members', [
        'team_id' => $team->id,
        'user_id' => $owner->id,
        'role' => TeamRole::Owner->value,
    ]);
    $this->assertDatabaseHas('team_members', [
        'team_id' => $team->id,
        'user_id' => $member->id,
        'role' => TeamRole::Member->value,
    ]);

    expect($owner->fresh()->current_team_id)->toBe($team->id);

    Notification::assertSentTo($owner, TeamMemberCredentials::class);
    Notification::assertSentTo($member, TeamMemberCredentials::class);
});

test('creating a team rejects an already used member email', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'taken@allianz.example.com']);

    $this->actingAs($admin);

    Livewire::test(TeamsPage::class)
        ->callAction('create', data: [
            'name' => 'Allianz Team',
            'slug' => 'allianz-team',
            'members' => [
                ['name' => 'Claire Mukendi', 'email' => 'taken@allianz.example.com', 'role' => TeamRole::Owner->value],
            ],
        ]);

    $this->assertDatabaseMissing('teams', ['slug' => 'allianz-team']);
    Notification::assertNothingSent();
});

test('admin can edit a team name and slug', function () {
    $admin = User::factory()->admin()->create();
    $team = Team::factory()->create(['name' => 'Ancienne équipe', 'slug' => 'ancienne-equipe']);

    $this->actingAs($admin);

    Livewire::test(TeamsPage::class)
        ->callTableAction('edit', $team, [
            'name' => 'Nouvelle équipe',
            'slug' => 'nouvelle-equipe',
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'Nouvelle équipe',
        'slug' => 'nouvelle-equipe',
    ]);
});

test('admin can delete a team from teams page', function () {
    $admin = User::factory()->admin()->create();
    $team = Team::factory()->create();

    $this->actingAs($admin);

    Livewire::test(TeamsPage::class)
        ->callTableAction('delete', $team);

    $this->assertSoftDeleted($team);
});

test('team member credentials notification points to the login route', function () {
    $member = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Allianz Team']);

    $mail = (new TeamMemberCredentials($team, 'TempPass123'))->toMail($member);

    expect($mail->actionUrl)->toBe(route('login'));
    expect($mail->introLines)->toContain('Mot de passe temporaire : TempPass123');
});
