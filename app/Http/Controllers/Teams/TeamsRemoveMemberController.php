<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamsRemoveMemberController extends Controller
{
    public function __invoke(Request $request, Team $team, int $userId): RedirectResponse
    {
        Gate::authorize('removeMember', $team);

        $member = User::findOrFail($userId);

        $team->members()->detach($member);

        if ($member->current_team_id === $team->id) {
            $member->switchTeam($member->personalTeam());
        }

        return back()->with('success', __('Member removed.'));
    }
}
