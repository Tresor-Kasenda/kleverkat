<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Dashboard', [
            'team' => $request->user()?->currentTeam ? [
                'id'   => $request->user()->currentTeam->id,
                'name' => $request->user()->currentTeam->name,
                'slug' => $request->user()->currentTeam->slug,
            ] : null,
        ]);
    }
}
