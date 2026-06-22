<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TeamsCancelInvitationController extends Controller
{
    public function __invoke(Team $team, TeamInvitation $invitation): RedirectResponse
    {
        Gate::authorize('cancelInvitation', $team);

        abort_if($invitation->team_id !== $team->id, 404);

        $invitation->delete();

        return back()->with('success', __('Invitation cancelled.'));
    }
}
