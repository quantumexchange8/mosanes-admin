<?php

use App\Http\Controllers\GroupController;
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
     *            Group
     * ==============================
     */
    Route::prefix('group')->group(function () {
        Route::get('/', [GroupController::class, 'show'])->name('group');

        Route::post('/create_group', [GroupController::class, 'createGroup'])->name('group.create');

        Route::put('/edit_group', [GroupController::class, 'editGroup'])->name('group.edit');

        Route::delete('/delete_group/{id}', [GroupController::class, 'deleteGroup'])->name('group.delete');
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

Route::get('/getData', function () {
//    $countries = \Illuminate\Support\Facades\DB::table('countries')->get()->map(function ($country) {
//        return [
//            'id' => $country->id,
//            'name' => $country->name,
//            'code' => $country->iso3,
//        ];
//    });

    $users = \App\Models\User::latest()->get();

    return response()->json([
        'users' => $users,
//        'countries' => $countries,
    ]);
});

require __DIR__.'/auth.php';
