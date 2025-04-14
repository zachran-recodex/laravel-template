<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\ArtisanCommand;
use App\Livewire\Settings\ManageMetaTags;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\UserActivity;
use App\Livewire\Settings\ViewLog;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;

Route::controller(MainController::class)->group(function () {

    Route::get('', 'index')->name('home');

});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::prefix('dashboard')->name('dashboard.')->group(function (){

        Route::prefix('administrator')->name('administrator.')->group(function (){

            Route::get('overview', App\Livewire\Administrator\Overview::class)
                ->name('overview');

            Route::get('manage-users', App\Livewire\Administrator\ManageUsers::class)
                ->name('user')
                ->middleware('can:manage users');

            Route::get('manage-roles', App\Livewire\Administrator\ManageRoles::class)
                ->name('role')
                ->middleware('can:manage roles');

            Route::get('manage-permissions', App\Livewire\Administrator\ManagePermissions::class)
                ->name('permission')
                ->middleware('can:manage permissions');
        });

    });

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    Route::get('settings/meta-tags', ManageMetaTags::class)->name('settings.meta-tags');
    Route::get('settings/view-log', ViewLog::class)->name('settings.view-log');
    Route::get('settings/user-activity', UserActivity::class)->name('settings.user-activity');
    Route::get('settings/artisan-command', ArtisanCommand::class)->name('settings.artisan-command');
});

require __DIR__.'/auth.php';
