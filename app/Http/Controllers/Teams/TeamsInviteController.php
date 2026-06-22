<?php

namespace App\Http\Controllers\Teams;

use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Notifications\Teams\TeamInvitation as TeamInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeamsInviteController extends Controller
{
    public function __invoke(Request $request, Team $team): RedirectResponse
    {
        Gate::authorize('inviteMember', $team);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::enum(TeamRole::class)->except(TeamRole::Owner)],
        ]);

        if ($team->members()->where('email', $validated['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => [__('This user is already a member of this team.')],
            ]);
        }

        $invitation = TeamInvitation::create([
            'team_id' => $team->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'invited_by' => $request->user()->id,
            'expires_at' => now()->addDays(7),
        ]);

        // Notify via a temporary anonymous notifiable targeting the email address
        Notification::route('mail', $validated['email'])
            ->notify(new TeamInvitationNotification($invitation));

        return back()->with('success', __('Invitation sent.'));
    }
}
