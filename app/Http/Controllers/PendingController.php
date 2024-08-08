<?php

namespace App\Http\Controllers;

use App\Models\TradingAccount;
use App\Models\TradingUser;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\ChangeTraderBalanceType;
use App\Services\CTraderService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PendingController extends Controller
{
    public function index()
    {
        return Inertia::render('Pending/Pending');
    }

    public function getPendingWithdrawalData(Request $request)
    {
        $pendingWithdrawals = Transaction::with([
            'user:id,email,name',
            'payment_account:id,payment_account_name,account_no',
        ])
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'processing')
            ->latest()
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'created_at' => $transaction->created_at,
                    'user_name' => $transaction->user->name,
                    'user_email' => $transaction->user->email,
                    'user_profile_photo' => $transaction->user->getFirstMediaUrl('profile_photo'),
                    'from' => $transaction->category == 'trading_account' ? $transaction->from_meta_login : 'rebate_wallet',
                    'amount' => $transaction->amount,
                    'transaction_charges' => $transaction->transaction_charges,
                    'transaction_amount' => $transaction->transaction_amount,
                    'wallet_name' => $transaction->payment_account->payment_account_name,
                    'wallet_address' => $transaction->payment_account->account_no,
                ];
            });

        $totalAmount = $pendingWithdrawals->sum('amount');

        return response()->json([
            'pendingWithdrawals' => $pendingWithdrawals,
            'totalAmount' => $totalAmount,
        ]);
    }

    public function withdrawalApproval(Request $request)
    {
        $action = $request->action;

        $status = $action == 'approve' ? 'successful' : 'rejected';

        $transaction = Transaction::find($request->id);

        if ($transaction->status != 'processing') {
            return redirect()->back()->with('toast', [
                'title' => 'Invalid action. Please try again.',
                'type' => 'warning'
            ]);
        }

        $transaction->update([
            'remarks' => $request->remarks,
            'status' => $status,
            'approved_at' => now(),
            'handle_by' => Auth::id()
        ]);

        if ($transaction->status == 'rejected') {

            if ($transaction->category == 'rebate_wallet') {
                $rebate_wallet = Wallet::where('user_id', $transaction->user_id)
                    ->where('type', 'rebate_wallet')
                    ->first();

                $transaction->update([
                    'old_wallet_amount' => $rebate_wallet->balance,
                    'new_wallet_amount' => $rebate_wallet->balance += $transaction->amount,
                ]);

                $rebate_wallet->save();
            }

            if ($transaction->category == 'trading_account') {

                try {
                    $trade = (new CTraderService)->createTrade($transaction->from_meta_login, $transaction->amount, $transaction->remarks, ChangeTraderBalanceType::DEPOSIT);

                    $transaction->update([
                        'ticket' => $trade->getTicket(),
                    ]);
                } catch (\Throwable $e) {
                    if ($e->getMessage() == "Not found") {
                        TradingUser::firstWhere('meta_login', $transaction->from_meta_login)->update(['acc_status' => 'Inactive']);
                    } else {
                        Log::error($e->getMessage());
                    }
                    return back()
                        ->with('toast', [
                            'title' => 'Trading account error',
                            'type' => 'error'
                        ]);
                }
            }

            return redirect()->back()->with('toast', [
                'title' => trans('public.toast_reject_withdrawal_request'),
                'type' => 'success'
            ]);
        } else {
            return redirect()->back()->with('toast', [
                'title' => trans('public.toast_approve_withdrawal_request'),
                'type' => 'success'
            ]);
        }
    }
}
