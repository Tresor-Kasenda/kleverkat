<?php

use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');

// Public comparison flow
Route::prefix('comparer')->name('compare.')->group(function () {
    // Results must be declared before {category:slug} to avoid routing collision
    Route::livewire('/', 'pages::compare.category-list')->name('categories');
    Route::livewire('/resultats/{session}', 'pages::compare.results')->name('results');
    Route::livewire('/{category:slug}', 'pages::compare.sector-list')->name('sectors');
    Route::livewire('/{category:slug}/{sector:slug}', 'pages::compare.product-list')->name('products');
    Route::livewire('/{category:slug}/{sector:slug}/{product:slug}', 'pages::compare.wizard')->name('wizard');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
        Route::livewire('company/profile', 'pages::companies.profile')->name('company.profile');
    });

Route::middleware(['auth'])->group(function () {
    Route::livewire('invitations/{invitation}/accept', 'pages::teams.accept-invitation')->name('invitations.accept');
});

require __DIR__.'/settings.php';
