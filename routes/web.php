<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

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
});

require __DIR__.'/auth.php';
