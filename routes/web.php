<?php

use App\Livewire\Admin\Roles\RoleIndex;
use App\Livewire\Admin\Users\UserIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Group Route Admin untuk Pineagrail
    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:superadmin|admin'])
        ->group(function () {
            Route::get('/users', UserIndex::class)->name('users.index');
        });

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:superadmin'])
        ->group(function () {
            Route::get('/roles', RoleIndex::class)->name('roles.index');
        });
});

require __DIR__.'/settings.php';
