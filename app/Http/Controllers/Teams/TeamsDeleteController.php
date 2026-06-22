<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TeamsDeleteController extends Controller
{
    public function __invoke(Request $request, Team $team): RedirectResponse
    {
        Gate::authorize('delete', $team);

        $request->validate([
            'name' => ['required', 'string'],
        ]);

        if ($request->input('name') !== $team->name) {
            throw ValidationException::withMessages([
                'name' => [__('The team name does not match.')],
            ]);
        }

        DB::transaction(function () use ($team) {
            $affectedUserIds = $team->members()->pluck('users.id');

            $team->delete();

            User::whereIn('id', $affectedUserIds)->each(function (User $user) use ($team) {
                if ($user->current_team_id !== $team->id) {
                    return;
                }

                $fallback = $user->teams()
                    ->whereNot('teams.id', $team->id)
                    ->orderBy('teams.name')
                    ->first()
                    ?? $user->personalTeam();

                $user->switchTeam($fallback);
            });
        });

        $user = $request->user();
        $currentTeam = $user->fresh()->currentTeam;

        if ($currentTeam) {
            return redirect()->route('teams.edit', $currentTeam->slug)
                ->with('success', __('Team deleted.'));
        }

        return redirect()->route('teams.index')
            ->with('success', __('Team deleted.'));
    }
}
