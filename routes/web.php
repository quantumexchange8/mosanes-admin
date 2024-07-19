<?php

use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect(route('login'));
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
        // listing
        Route::get('/listing', [MemberController::class, 'listing'])->name('member.listing');
        Route::post('/addNewMember', [MemberController::class, 'addNewMember'])->name('member.addNewMember');

        // details
        Route::get('/detail', [MemberController::class, 'detail'])->name('member.detail');
        Route::get('/loadCountries', [MemberController::class, 'loadCountries'])->name('member.loadCountries');
        Route::post('/updateContactInfo', [MemberController::class, 'updateContactInfo'])->name('member.updateContactInfo');
        Route::post('/updateCryptoWalletInfo', [MemberController::class, 'updateCryptoWalletInfo'])->name('member.updateCryptoWalletInfo');
        Route::post('/updateKYCStatus', [MemberController::class, 'updateKYCStatus'])->name('member.updateKYCStatus');
        Route::post('/cashWalletAdjustment', [MemberController::class, 'cashWalletAdjustment'])->name('member.cashWalletAdjustment');
        Route::post('/rebateWalletAdjustment', [MemberController::class, 'rebateWalletAdjustment'])->name('member.rebateWalletAdjustment');
        Route::post('/accountBalanceAdjustment', [MemberController::class, 'accountBalanceAdjustment'])->name('member.accountBalanceAdjustment');
        Route::post('/accountCreditAdjustment', [MemberController::class, 'accountCreditAdjustment'])->name('member.accountCreditAdjustment');
        Route::post('/accountDelete', [MemberController::class, 'accountDelete'])->name('member.accountDelete');
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

Route::get('/test/component', function () {
    return Inertia::render('Welcome');
})->name('test.component');

Route::get('/test/getData', function () {
    $users = \App\Models\User::all();
    return response()->json([
        'users' => $users
    ]);
});

require __DIR__.'/auth.php';
