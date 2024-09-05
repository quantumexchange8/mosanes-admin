<?php

namespace App\Http\Controllers;

use App\Models\TradingAccount;
use App\Models\TradingUser;
use App\Models\Transaction;
use App\Services\ChangeTraderBalanceType;
use App\Services\CTraderService;
use App\Services\RunningNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TradingAccountController extends Controller
{
    public function index()
    {
        return Inertia::render('Member/Account/AccountListing');
    }

    public function getAccountListingData(Request $request)
    {
        if ($request->account_listing == 'all') {
            $accounts = TradingUser::with([
                'user:id,name,email',
                'trading_account:id,meta_login,equity'
            ])
                ->orderByDesc('last_access')
                ->get()
                ->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'meta_login' => $account->meta_login,
                        'user_name' => $account->user->name,
                        'user_email' => $account->user->email,
                        'user_profile_photo' => $account->user->getFirstMediaUrl('profile_photo'),
                        'balance' => $account->balance,
                        'equity' => $account->trading_account->equity,
                        'last_login' => intval($account->last_access),
                    ];
                });
        } else {
            $accounts = TradingAccount::withTrashed();
        }

        return response()->json([
            'accounts' => $accounts
        ]);
    }

    public function getTradingAccountData(Request $request)
    {
        $conn = (new CTraderService)->connectionStatus();
        if ($conn['code'] != 0) {
            return back()
                ->with('toast', [
                    'title' => 'Connection Error',
                    'type' => 'error'
                ]);
        }

        $account = TradingAccount::find($request->account_id);

        try {
            (new CTraderService)->getUserInfo($account->meta_login);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }

        return response()->json([
            'currentAmount' => $request->dialog_type == 'account_balance' ? $account->balance : $account->credit,
        ]);
    }

    public function accountAdjustment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => ['required'],
            'amount' => ['required', 'numeric', 'gt:1'],
            'remarks' => ['nullable'],
        ])->setAttributeNames([
            'action' => trans('public.action'),
            'amount' => trans('public.amount'),
            'remarks' => trans('public.remarks'),
        ]);
        $validator->validate();

        $cTraderService = (new CTraderService);

        $conn = $cTraderService->connectionStatus();
        if ($conn['code'] != 0) {
            return back()
                ->with('toast', [
                    'title' => 'Connection Error',
                    'type' => 'error'
                ]);
        }

        $trading_account = TradingAccount::find($request->account_id);
        try {
            $cTraderService->getUserInfo($trading_account->meta_login);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            return back()
                ->with('toast', [
                    'title' => 'No Account Found',
                    'type' => 'error'
                ]);
        }

        $action = $request->action;
        $type = $request->type;
        $amount = $request->amount;

        if ($type === 'account_balance' && $action === 'balance_out' && $trading_account->balance < $amount) {
            throw ValidationException::withMessages(['amount' => trans('public.insufficient_balance')]);
        }

        if ($type === 'account_credit' && $action === 'credit_out' && $trading_account->credit < $amount) {
            throw ValidationException::withMessages(['amount' => trans('public.insufficient_credit')]);
        }

        $transaction = Transaction::create([
            'user_id' => $trading_account->user_id,
            'category' => 'trading_account',
            'transaction_type' => $action,
            'from_meta_login' => ($action === 'balance_out' || $action === 'credit_out') ? $trading_account->meta_login : null,
            'to_meta_login' => ($action === 'balance_in' || $action === 'credit_in') ? $trading_account->meta_login : null,
            'transaction_number' => RunningNumberService::getID('transaction'),
            'amount' => $amount,
            'transaction_amount' => $amount,
            'status' => 'processing',
            'remarks' => $request->remarks,
            'handle_by' => Auth::id(),
        ]);

        $changeType = match($type) {
            'account_balance' => match($action) {
                'balance_in' => ChangeTraderBalanceType::DEPOSIT,
                'balance_out' => ChangeTraderBalanceType::WITHDRAW,
                default => throw ValidationException::withMessages(['action' => trans('public.invalid_type')]),
            },
            'account_credit' => match($action) {
                'credit_in' => ChangeTraderBalanceType::DEPOSIT_NONWITHDRAWABLE_BONUS,
                'credit_out' => ChangeTraderBalanceType::WITHDRAW_NONWITHDRAWABLE_BONUS,
                default => throw ValidationException::withMessages(['action' => trans('public.invalid_type')]),
            },
            default => throw ValidationException::withMessages(['action' => trans('public.invalid_type')]),
        };

        try {
            $trade = (new CTraderService)->createTrade($trading_account->meta_login, $amount, $transaction->remarks, $changeType);

            $transaction->update([
                'ticket' => $trade->getTicket(),
                'status' => 'successful',
            ]);

            return redirect()->back()->with('toast', [
                'title' => $type == 'account_balance' ? trans('public.toast_balance_adjustment_success') : trans('public.toast_credit_adjustment_success'),
                'type' => 'success'
            ]);
        } catch (\Throwable $e) {
            // Update transaction status to failed on error
            $transaction->update(['status' => 'failed']);

            // Handle specific error cases
            if ($e->getMessage() == "Not found") {
                TradingUser::firstWhere('meta_login', $trading_account->meta_login)->update(['acc_status' => 'Inactive']);
            } else {
                Log::error($e->getMessage());
            }

            return response()->json([
                'message' => 'Account adjustment failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
