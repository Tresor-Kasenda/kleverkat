<?php

use App\Http\Controllers\Settings\AppearanceController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Teams\TeamsCancelInvitationController;
use App\Http\Controllers\Teams\TeamsDeleteController;
use App\Http\Controllers\Teams\TeamsEditController;
use App\Http\Controllers\Teams\TeamsIndexController;
use App\Http\Controllers\Teams\TeamsInviteController;
use App\Http\Controllers\Teams\TeamsRemoveMemberController;
use App\Http\Controllers\Teams\TeamsStoreController;
use App\Http\Controllers\Teams\TeamsUpdateMemberController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'show'])->name('profile.edit');
    Route::put('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('settings/profile/verify', [ProfileController::class, 'resendVerification'])->name('verification.resend');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('settings/appearance', AppearanceController::class)->name('appearance.edit');

    Route::get(
        'settings/security',
        [SecurityController::class, 'show'],
    )->middleware(
        when(
            Features::canManageTwoFactorAuthentication()
            && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
            ['password.confirm'],
            [],
        ),
    )->name('security.edit');

    Route::put('settings/security/password', [SecurityController::class, 'updatePassword'])->name('security.password.update');

    Route::get('settings/teams', TeamsIndexController::class)->name('teams.index');
    Route::post('settings/teams', TeamsStoreController::class)->name('teams.store');

    Route::middleware(EnsureTeamMembership::class)->group(function () {
        Route::get('settings/teams/{team}', [TeamsEditController::class, 'show'])->name('teams.edit');
        Route::put('settings/teams/{team}', [TeamsEditController::class, 'update'])->name('teams.update');
        Route::delete('settings/teams/{team}', TeamsDeleteController::class)->name('teams.delete');
        Route::post('settings/teams/{team}/invite', TeamsInviteController::class)->name('teams.invite');
        Route::delete('settings/teams/{team}/invitations/{invitation}', TeamsCancelInvitationController::class)->name('teams.invitations.cancel');
        Route::put('settings/teams/{team}/members/{userId}', TeamsUpdateMemberController::class)->name('teams.members.update');
        Route::delete('settings/teams/{team}/members/{userId}', TeamsRemoveMemberController::class)->name('teams.members.remove');
    });
});
