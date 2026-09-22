<?php

use App\Livewire\Admin\Users\UserIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Group Route Admin untuk Pineagrail
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', UserIndex::class)->name('users.index');
    });
});

require __DIR__.'/settings.php';
