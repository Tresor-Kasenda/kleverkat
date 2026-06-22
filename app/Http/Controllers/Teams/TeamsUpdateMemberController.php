<?php

namespace App\Http\Controllers\Teams;

use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TeamsUpdateMemberController extends Controller
{
    public function __invoke(Request $request, Team $team, int $userId): RedirectResponse
    {
        Gate::authorize('updateMember', $team);

        $validated = $request->validate([
            'role' => ['required', Rule::enum(TeamRole::class)],
        ]);

        $team->memberships()
            ->where('user_id', $userId)
            ->firstOrFail()
            ->update(['role' => TeamRole::from($validated['role'])]);

        return back()->with('success', __('Member role updated.'));
    }
}
