<?php

namespace App\Http\Controllers\Teams;

use App\Actions\Teams\CreateTeam;
use App\Http\Controllers\Controller;
use App\Rules\TeamName;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamsStoreController extends Controller
{
    public function __invoke(Request $request, CreateTeam $createTeam): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', new TeamName],
        ]);

        $team = $createTeam->handle($request->user(), $validated['name']);

        return redirect()->route('teams.edit', $team->slug)
            ->with('success', __('Team created.'));
    }
}
