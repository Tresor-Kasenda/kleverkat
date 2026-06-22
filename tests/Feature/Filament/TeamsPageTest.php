<?php

use App\Actions\Teams\CreateTeamWithMembers;
use App\Enums\TeamRole;
use App\Filament\Pages\TeamsPage;
use App\Models\Team;
use App\Models\User;
use App\Notifications\Teams\TeamMemberCredentials;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
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

test('create team action provisions member accounts and notifies them', function () {
    Notification::fake();

    $team = app(CreateTeamWithMembers::class)->handle([
        'name' => 'Allianz Team',
        'slug' => 'allianz-team',
        'members' => [
            ['name' => 'Claire Mukendi', 'email' => 'claire@allianz.example.com', 'role' => TeamRole::Owner->value, 'password' => 'Password123!'],
            ['name' => 'Paul Ilunga', 'email' => 'paul@allianz.example.com', 'role' => TeamRole::Member->value, 'password' => 'Secret456!'],
        ],
    ]);

    expect($team->is_personal)->toBeFalse();
    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'slug' => 'allianz-team',
        'is_personal' => false,
    ]);

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
    expect(Hash::check('Password123!', $owner->fresh()->password))->toBeTrue();

    Notification::assertSentTo($owner, TeamMemberCredentials::class);
    Notification::assertSentTo($member, TeamMemberCredentials::class);
});

test('create team action rolls back when a member email is already used', function () {
    Notification::fake();

    User::factory()->create(['email' => 'taken@allianz.example.com']);

    $call = fn () => app(CreateTeamWithMembers::class)->handle([
        'name' => 'Allianz Team',
        'slug' => 'allianz-team',
        'members' => [
            ['name' => 'Claire Mukendi', 'email' => 'taken@allianz.example.com', 'role' => TeamRole::Owner->value, 'password' => 'Password123!'],
        ],
    ]);

    expect($call)->toThrow(QueryException::class);

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
