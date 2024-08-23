<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PendingController;
use Inertia\Inertia;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PammController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\RebateController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\TransactionController;

Route::get('locale/{locale}', function ($locale) {
    App::setLocale($locale);
    Session::put("locale", $locale);

    return redirect()->back();
});

Route::get('/', function () {
    return redirect(route('login'));
});

Route::middleware(['auth', 'role:super-admin|admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/getPendingCounts', [DashboardController::class, 'getPendingCounts'])->name('dashboard.getPendingCounts');
    Route::get('/getOptions', [DashboardController::class, 'getOptions'])->name('dashboard.getOptions');
    Route::get('/getAccountData', [DashboardController::class, 'getAccountData'])->name('dashboard.getAccountData');
    Route::get('/getPendingData', [DashboardController::class, 'getPendingData'])->name('dashboard.getPendingData');
    Route::get('/getAssetData', [DashboardController::class, 'getAssetData'])->name('dashboard.getAssetData');

    /**
     * ==============================
     *           Pending
     * ==============================
     */
    Route::prefix('pending')->group(function () {
        Route::get('/', [PendingController::class, 'index'])->name('pending');
        Route::get('/getPendingWithdrawalData', [PendingController::class, 'getPendingWithdrawalData'])->name('pending.getPendingWithdrawalData');

        Route::post('withdrawalApproval', [PendingController::class, 'withdrawalApproval'])->name('pending.withdrawalApproval');
    });

    /**
     * ==============================
     *           Member
     * ==============================
     */
    Route::prefix('member')->group(function () {
        // listing
        Route::get('/listing', [MemberController::class, 'listing'])->name('member.listing');
        Route::get('/getMemberListingData', [MemberController::class, 'getMemberListingData'])->name('member.getMemberListingData');
        Route::get('/getFilterData', [MemberController::class, 'getFilterData'])->name('member.getFilterData');
        Route::get('/getAvailableUplineData', [MemberController::class, 'getAvailableUplineData'])->name('member.getAvailableUplineData');
        Route::get('/access_portal/{user}', [MemberController::class, 'access_portal'])->name('member.access_portal');

        Route::post('/addNewMember', [MemberController::class, 'addNewMember'])->name('member.addNewMember');
        Route::post('/updateMemberStatus', [MemberController::class, 'updateMemberStatus'])->name('member.updateMemberStatus');
        Route::post('/upgradeAgent', [MemberController::class, 'upgradeAgent'])->name('member.upgradeAgent');
        Route::post('/uploadKyc', [MemberController::class, 'uploadKyc'])->name('member.uploadKyc');

        Route::delete('/deleteMember', [MemberController::class, 'deleteMember'])->name('member.deleteMember');
        // details
        Route::get('/detail/{id_number}', [MemberController::class, 'detail'])->name('member.detail');
        Route::get('/getUserData', [MemberController::class, 'getUserData'])->name('member.getUserData');
        Route::get('/getFinancialInfoData', [MemberController::class, 'getFinancialInfoData'])->name('member.getFinancialInfoData');
        Route::get('/getTradingAccounts', [MemberController::class, 'getTradingAccounts'])->name('member.getTradingAccounts');
        Route::get('/getAdjustmentHistoryData', [MemberController::class, 'getAdjustmentHistoryData'])->name('member.getAdjustmentHistoryData');

        Route::post('/updateContactInfo', [MemberController::class, 'updateContactInfo'])->name('member.updateContactInfo');
        Route::post('/updateCryptoWalletInfo', [MemberController::class, 'updateCryptoWalletInfo'])->name('member.updateCryptoWalletInfo');
        Route::post('/updateKYCStatus', [MemberController::class, 'updateKYCStatus'])->name('member.updateKYCStatus');
        Route::post('/walletAdjustment', [MemberController::class, 'walletAdjustment'])->name('member.walletAdjustment');
        Route::post('/accountAdjustment', [MemberController::class, 'accountAdjustment'])->name('member.accountAdjustment');
        Route::post('/accountDelete', [MemberController::class, 'accountDelete'])->name('member.accountDelete');

        // network
        Route::get('/network', [NetworkController::class, 'network'])->name('member.network');
        Route::get('/getDownlineData', [NetworkController::class, 'getDownlineData'])->name('member.getDownlineData');
    });

    /**
     * ==============================
     *            Group
     * ==============================
     */
    Route::prefix('group')->group(function () {
        Route::get('/', [GroupController::class, 'show'])->name('group');
        Route::get('/getGroups', [GroupController::class, 'getGroups'])->name('group.getGroups');
        Route::get('/getAgents', [GroupController::class, 'getAgents'])->name('group.getAgents');
        Route::get('/getGroupTransaction', [GroupController::class, 'getGroupTransaction'])->name('group.getGroupTransaction');

        Route::post('/create_group', [GroupController::class, 'createGroup'])->name('group.create');

        Route::patch('/edit_group/{id}', [GroupController::class, 'editGroup'])->name('group.edit');

        Route::delete('/delete_group/{id}', [GroupController::class, 'deleteGroup'])->name('group.delete');
    });

    /**
     * ==============================
     *        Pamm Allocate
     * ==============================
     */
    Route::prefix('pamm_allocate')->group(function () {
        Route::get('/', [PammController::class, 'pamm_allocate'])->name('pamm_allocate');
        Route::get('/getMasters', [PammController::class, 'getMasters'])->name('pamm_allocate.getMasters');
        Route::get('/getMetrics', [PammController::class, 'getMetrics'])->name('pamm_allocate.getMetrics');
        Route::get('/getOptions', [PammController::class, 'getOptions'])->name('pamm_allocate.getOptions');
        Route::get('/getProfitLoss', [PammController::class, 'getProfitLoss'])->name('pamm_allocate.getProfitLoss');
        Route::get('/getJoiningPammAccountsData', [PammController::class, 'getJoiningPammAccountsData'])->name('pamm_allocate.getJoiningPammAccountsData');
        Route::get('/getMasterMonthlyProfit', [PammController::class, 'getMasterMonthlyProfit'])->name('pamm_allocate.getMasterMonthlyProfit');

        Route::post('/upload_image', [PammController::class, 'upload_image'])->name('pamm_allocate.upload_image');
        Route::post('/validateStep', [PammController::class, 'validateStep'])->name('pamm_allocate.validateStep');
        Route::post('/create_asset_master', [PammController::class, 'create_asset_master'])->name('pamm_allocate.create_asset_master');
        Route::post('/edit_asset_master', [PammController::class, 'edit_asset_master'])->name('pamm_allocate.edit_asset_master');
        Route::post('/update_asset_master_status', [PammController::class, 'update_asset_master_status'])->name('pamm_allocate.update_asset_master_status');
        Route::post('/disband', [PammController::class, 'disband'])->name('pamm_allocate.disband');
        Route::post('/updateLikeCounts', [PammController::class, 'updateLikeCounts'])->name('pamm_allocate.updateLikeCounts');
        Route::post('/addProfitDistribution', [PammController::class, 'addProfitDistribution'])->name('pamm_allocate.addProfitDistribution');
    });

    /**
     * ==============================
     *        Rebate Allocate
     * ==============================
     */
    Route::prefix('rebate_allocate')->group(function () {
        Route::get('/', [RebateController::class, 'rebate_allocate'])->name('rebate_allocate');
        Route::get('/getCompanyProfileData', [RebateController::class, 'getCompanyProfileData'])->name('rebate_allocate.getCompanyProfileData');
        Route::get('/getRebateStructureData', [RebateController::class, 'getRebateStructureData'])->name('rebate_allocate.getRebateStructureData');
        Route::get('/getAgents', [RebateController::class, 'getAgents'])->name('rebate_allocate.getAgents');
        Route::get('/changeAgents', [RebateController::class, 'changeAgents'])->name('rebate_allocate.changeAgents');

        Route::post('/updateRebateAllocation', [RebateController::class, 'updateRebateAllocation'])->name('rebate_allocate.updateRebateAllocation');
        Route::post('/updateRebateAmount', [RebateController::class, 'updateRebateAmount'])->name('rebate_allocate.updateRebateAmount');
    });

    /**
     * ==============================
     *          Transaction
     * ==============================
     */
    Route::prefix('transaction')->group(function () {
        Route::get('/', [TransactionController::class, 'listing'])->name('transaction');
        Route::get('/getTransactionListingData', [TransactionController::class, 'getTransactionListingData'])->name('transaction.getTransactionListingData');
        Route::get('/getTransactionMonths', [TransactionController::class, 'getTransactionMonths'])->name('transaction.getTransactionMonths');

    });

    /**
     * ==============================
     *         Account Type
     * ==============================
     */
    Route::prefix('account_type')->group(function () {
        Route::get('/', [AccountTypeController::class, 'show'])->name('accountType');
        Route::get('/getAccountTypes', [AccountTypeController::class, 'getAccountTypes'])->name('accountType.getAccountTypes');
        Route::get('/syncAccountTypes', [AccountTypeController::class, 'syncAccountTypes'])->name('accountType.syncAccountTypes');
        Route::get('/findAccountType/{id}', [AccountTypeController::class, 'findAccountType'])->name('accountType.findAccountType');
        Route::get('/getLevearges', [AccountTypeController::class, 'getLeverages'])->name('accountType.getLeverages');

        Route::post('/update/{id}', [AccountTypeController::class, 'updateAccountType'])->name('accountType.update');

        Route::patch('/updateStatus/{id}', [AccountTypeController::class, 'updateStatus'])->name('accountType.updateStatus');
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

require __DIR__.'/auth.php';
