<?php

use App\Http\Controllers\Compare\CategoryListController;
use App\Http\Controllers\Compare\ProductListController;
use App\Http\Controllers\Compare\ResultsController;
use App\Http\Controllers\Compare\SectorListController;
use App\Http\Controllers\Compare\WizardController;
use App\Http\Controllers\Companies\CompanyProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Teams\AcceptInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Public comparison flow
Route::prefix('comparer')->name('compare.')->group(function () {
    Route::get('/', CategoryListController::class)->name('categories');
    Route::get('/{category:slug}', SectorListController::class)->name('sectors');
    Route::get('/{category:slug}/{sector:slug}', ProductListController::class)->name('products');
    Route::get('/{category:slug}/{sector:slug}/{product:slug}', [WizardController::class, 'show'])->name('wizard');
    Route::post('/{category:slug}/{sector:slug}/{product:slug}', [WizardController::class, 'store'])->name('wizard.store');
    Route::get('/resultats/{session}', [ResultsController::class, 'show'])->name('results');
    Route::post('/leads/{result}', [ResultsController::class, 'createLead'])->name('leads.create')->middleware('auth');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::get('company/profile', [CompanyProfileController::class, 'show'])->name('company.profile');
        Route::put('company/profile', [CompanyProfileController::class, 'update'])->name('company.profile.update');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', AcceptInvitationController::class)->name('invitations.accept');
});

require __DIR__.'/settings.php';
