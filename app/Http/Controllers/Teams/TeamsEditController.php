<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Rules\TeamName;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TeamsEditController extends Controller
{
    public function show(Team $team): Response
    {
        $team->load(['members', 'invitations' => fn ($q) => $q->where('accepted_at', null)]);

        return Inertia::render('Teams/Edit', [
            'team'    => $this->serializeTeam($team),
            'members' => $team->members->map(fn ($member) => [
                'id'     => $member->id,
                'name'   => $member->name,
                'email'  => $member->email,
                'role'   => $member->pivot->role,
            ])->values(),
            'invitations' => $team->invitations->map(fn ($inv) => [
                'id'    => $inv->id,
                'email' => $inv->email,
                'role'  => $inv->role,
            ])->values(),
            'availableRoles' => \App\Enums\TeamRole::cases()->map(fn ($r) => [
                'value' => $r->value,
                'label' => $r->label(),
            ])->all(),
        ]);
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        Gate::authorize('update', $team);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', new TeamName],
        ]);

        DB::transaction(function () use ($team, $validated) {
            $team = Team::whereKey($team->id)->lockForUpdate()->firstOrFail();
            $team->update(['name' => $validated['name']]);
        });

        return redirect()->route('teams.edit', $team->fresh()->slug)
            ->with('success', __('Team updated.'));
    }

    /** @return array<string, mixed> */
    private function serializeTeam(Team $team): array
    {
        return [
            'id'          => $team->id,
            'name'        => $team->name,
            'slug'        => $team->slug,
            'is_personal' => $team->is_personal,
        ];
    }
}
