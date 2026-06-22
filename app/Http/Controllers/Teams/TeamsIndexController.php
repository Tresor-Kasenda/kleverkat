<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Support\UserTeam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamsIndexController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $teams = $request->user()->toUserTeams(includeCurrent: true)->map(fn (UserTeam $team) => [
            'id'         => $team->id,
            'name'       => $team->name,
            'slug'       => $team->slug,
            'role'       => $team->role,
            'roleLabel'  => $team->roleLabel,
            'isPersonal' => $team->isPersonal,
        ]);

        return Inertia::render('Teams/Index', [
            'teams' => $teams,
        ]);
    }
}
