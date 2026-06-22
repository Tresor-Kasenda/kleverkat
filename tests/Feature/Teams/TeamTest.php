<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;

test('teams index page can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('teams.index'))
        ->assertOk();
});

test('teams can be created', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('teams.store'), ['name' => 'Test Team'])
        ->assertRedirect();

    $this->assertDatabaseHas('teams', [
        'name' => 'Test Team',
        'is_personal' => false,
    ]);
});

test('team slug uses next available suffix', function () {
    $user = User::factory()->create();

    Team::factory()->create(['name' => 'Acme', 'slug' => 'acme']);
    Team::factory()->create(['name' => 'Acme One', 'slug' => 'acme-1']);
    Team::factory()->create(['name' => 'Acme Ten', 'slug' => 'acme-10']);

    $this->actingAs($user)
        ->post(route('teams.store'), ['name' => 'Acme'])
        ->assertRedirect();

    $this->assertDatabaseHas('teams', [
        'name' => 'Acme',
        'slug' => 'acme-11',
    ]);
});

test('team edit page can be rendered', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user)
        ->get(route('teams.edit', $team))
        ->assertOk();
});

test('teams can be updated by owners', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Original Name']);
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user)
        ->put(route('teams.update', $team), ['name' => 'Updated Name'])
        ->assertRedirect();

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'Updated Name',
    ]);
});

test('teams cannot be updated by members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($member)
        ->put(route('teams.update', $team), ['name' => 'Updated Name'])
        ->assertForbidden();
});

test('teams can be deleted by owners', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user)
        ->delete(route('teams.delete', $team), ['name' => $team->name])
        ->assertRedirect();

    $this->assertSoftDeleted('teams', ['id' => $team->id]);
});

test('team deletion requires name confirmation', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user)
        ->delete(route('teams.delete', $team), ['name' => 'Wrong Name'])
        ->assertSessionHasErrors('name');

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'deleted_at' => null,
    ]);
});

test('deleting current team switches to alphabetically first remaining team', function () {
    $user = User::factory()->create(['name' => 'Mike']);

    $zuluTeam = Team::factory()->create(['name' => 'Zulu Team']);
    $alphaTeam = Team::factory()->create(['name' => 'Alpha Team']);
    $betaTeam = Team::factory()->create(['name' => 'Beta Team']);

    $zuluTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);
    $alphaTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);
    $betaTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $user->update(['current_team_id' => $zuluTeam->id]);

    $this->actingAs($user)
        ->delete(route('teams.delete', $zuluTeam), ['name' => $zuluTeam->name])
        ->assertRedirect();

    $this->assertSoftDeleted('teams', ['id' => $zuluTeam->id]);
    expect($user->fresh()->current_team_id)->toEqual($alphaTeam->id);
});

test('deleting current team falls back to personal team when alphabetically first', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();
    $team = Team::factory()->create(['name' => 'Zulu Team']);
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $user->update(['current_team_id' => $team->id]);

    $this->actingAs($user)
        ->delete(route('teams.delete', $team), ['name' => $team->name])
        ->assertRedirect();

    $this->assertSoftDeleted('teams', ['id' => $team->id]);
    expect($user->fresh()->current_team_id)->toEqual($personalTeam->id);
});

test('deleting non current team leaves current team unchanged', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $user->update(['current_team_id' => $personalTeam->id]);

    $this->actingAs($user)
        ->delete(route('teams.delete', $team), ['name' => $team->name])
        ->assertRedirect();

    $this->assertSoftDeleted('teams', ['id' => $team->id]);
    expect($user->fresh()->current_team_id)->toEqual($personalTeam->id);
});

test('deleting team switches other affected users to their personal team', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $owner->update(['current_team_id' => $team->id]);
    $member->update(['current_team_id' => $team->id]);

    $this->actingAs($owner)
        ->delete(route('teams.delete', $team), ['name' => $team->name])
        ->assertRedirect();

    expect($member->fresh()->current_team_id)->toEqual($member->personalTeam()->id);
});

test('personal teams cannot be deleted', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();

    $this->actingAs($user)
        ->delete(route('teams.delete', $personalTeam), ['name' => $personalTeam->name])
        ->assertForbidden();

    $this->assertDatabaseHas('teams', [
        'id' => $personalTeam->id,
        'deleted_at' => null,
    ]);
});

test('teams cannot be deleted by non owners', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($member)
        ->delete(route('teams.delete', $team), ['name' => $team->name])
        ->assertForbidden();
});

test('guests cannot access teams', function () {
    $this->get(route('teams.index'))
        ->assertRedirect(route('login'));
});
