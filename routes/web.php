<?php

use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Dashboard');
    })->name('dashboard');

    /**
     * ==============================
     *           Member
     * ==============================
     */
    Route::prefix('member')->group(function () {
        Route::get('/listing', [MemberController::class, 'listing'])->name('member.listing');
//        Route::get('/network', [ProfileController::class, 'edit'])->name('member.network');
//        Route::get('/forum', [ProfileController::class, 'edit'])->name('member.forum');
    });

    /**
     * ==============================
     *           Profile
     * ==============================
     */
    Route::prefix('profile')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');

        Route::post('/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/delete_profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

Route::get('/components/buttons', function () {
    return Inertia::render('Components/Buttons');
})->name('components.buttons');

require __DIR__.'/auth.php';
