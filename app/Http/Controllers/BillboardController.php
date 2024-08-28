<?php

namespace App\Http\Controllers;

use App\Models\BillboardProfile;
use App\Models\TradeBrokerHistory;
use App\Models\TradingAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class BillboardController extends Controller
{
    public function index()
    {
        $profileCount = BillboardProfile::count();

        return Inertia::render('Billboard/BillboardListing', [
            'profileCount' => $profileCount,
        ]);
    }

    public function getBonusProfiles(Request $request)
    {
        $bonusQuery = BillboardProfile::query();

        $search = $request->search;
        $sales_calculation_mode = $request->sales_calculation_mode;
        $sales_category = $request->sales_category;

        if (!empty($search)) {
            $bonusQuery->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('id_number', 'like', '%' . $search . '%');
            });
        }

        if (!empty($sales_calculation_mode)) {
            $bonusQuery->where('sales_calculation_mode', $sales_calculation_mode);
        }

        if (!empty($sales_category)) {
            $bonusQuery->where('sales_category', $sales_category);
        }

        $totalRecords = $bonusQuery->count();

        $profiles = $bonusQuery->paginate($request->paginate);

        $formattedProfiles = $profiles->map(function($profile) {
            $bonus_amount = 0;
            $achieved_percentage = 0;
            $achieved_amount = 0;

            // Calculate bonus amount based on sales_calculation_mode and sales_category
            if ($profile->sales_calculation_mode == 'personal_sales') {
                if ($profile->sales_category == 'gross_deposit') {
                    $gross_deposit = Transaction::where('user_id', $profile->user_id)
                        ->where('transaction_type', 'deposit')
                        ->whereMonth('approved_at', date('m'))
                        ->where('status', 'successful')
                        ->sum('transaction_amount');

                    $achieved_percentage = ($gross_deposit / $profile->target_amount) * 100;
                    $bonus_amount = ($gross_deposit * $profile->bonus_rate) / 100;
                    $achieved_amount = $gross_deposit;
                } elseif ($profile->sales_category == 'net_deposit') {
                    $total_deposit = Transaction::where('user_id', $profile->user_id)
                        ->where('transaction_type', 'deposit')
                        ->whereMonth('approved_at', date('m'))
                        ->where('status', 'successful')
                        ->sum('transaction_amount');

                    $total_withdrawal = Transaction::where('user_id', $profile->user_id)
                        ->where('transaction_type', 'withdrawal')
                        ->whereMonth('approved_at', date('m'))
                        ->where('status', 'successful')
                        ->sum('transaction_amount');

                    $net_deposit = abs($total_deposit - $total_withdrawal);

                    $achieved_percentage = ($net_deposit / $profile->target_amount) * 100;
                    $bonus_amount = ($net_deposit * $profile->bonus_rate) / 100;
                    $achieved_amount = $net_deposit;
                } elseif ($profile->sales_category == 'trade_volume') {
                    $meta_logins = $profile->user->tradingAccounts->pluck('meta_login');

                    $trade_volume = TradeBrokerHistory::whereIn('meta_login', $meta_logins)
                        ->sum('trade_lots');

                    $achieved_percentage = ($trade_volume / $profile->target_amount) * 100;
                    $bonus_amount = $achieved_amount >= $profile->bonus_calculation_threshold ? $profile->bonus_rate : 0;
                    $achieved_amount = $trade_volume;
                }
            } elseif ($profile->sales_calculation_mode == 'group_sales') {
                if ($profile->sales_category == 'gross_deposit') {
                    $child_ids = $profile->user->getChildrenIds();
                    $child_ids[] = $profile->user_id;

                    $gross_deposit = Transaction::whereIn('user_id', $child_ids)
                        ->where('transaction_type', 'deposit')
                        ->whereMonth('approved_at', date('m'))
                        ->where('status', 'successful')
                        ->sum('transaction_amount');

                    $achieved_percentage = ($gross_deposit / $profile->target_amount) * 100;
                    $bonus_amount = ($gross_deposit * $profile->bonus_rate) / 100;
                    $achieved_amount = $gross_deposit;
                } elseif ($profile->sales_category == 'net_deposit') {
                    $child_ids = $profile->user->getChildrenIds();
                    $child_ids[] = $profile->user_id;

                    $total_deposit = Transaction::whereIn('user_id', $child_ids)
                        ->where('transaction_type', 'deposit')
                        ->whereMonth('approved_at', date('m'))
                        ->where('status', 'successful')
                        ->sum('transaction_amount');

                    $total_withdrawal = Transaction::whereIn('user_id', $child_ids)
                        ->where('transaction_type', 'withdrawal')
                        ->whereMonth('approved_at', date('m'))
                        ->where('status', 'successful')
                        ->sum('transaction_amount');

                    $net_deposit = abs($total_deposit - $total_withdrawal);

                    $achieved_percentage = ($net_deposit / $profile->target_amount) * 100;
                    $bonus_amount = ($net_deposit * $profile->bonus_rate) / 100;
                    $achieved_amount = $net_deposit;
                } elseif ($profile->sales_category == 'trade_volume') {
                    $child_ids = $profile->user->getChildrenIds();
                    $child_ids[] = $profile->user_id;

                    $meta_logins = TradingAccount::whereIn('user_id', $child_ids)
                        ->get()
                        ->pluck('meta_login')
                        ->toArray();

                    $trade_volume = TradeBrokerHistory::whereIn('meta_login', $meta_logins)
                        ->sum('trade_lots');

                    $achieved_percentage = ($trade_volume / $profile->target_amount) * 100;
                    $bonus_amount = $achieved_amount >= $profile->bonus_calculation_threshold ? $profile->bonus_rate : 0;
                    $achieved_amount = $trade_volume;
                }
            }

            return [
                'id' => $profile->id,
                'name' => $profile->user->name,
                'email' => $profile->user->email,
                'profile_photo' => $profile->user->getFirstMediaUrl('profile_photo'),
                'sales_calculation_mode' => $profile->sales_calculation_mode == 'personal_sales' ? 'personal' : 'group',
                'bonus_badge' => $profile->sales_calculation_mode == 'personal_sales' ? 'gray' : 'info',
                'sales_category' => $profile->sales_category,
                'target_amount' => $profile->target_amount,
                'bonus_amount' => $bonus_amount,
                'achieved_percentage' => $achieved_percentage,
                'achieved_amount' => $achieved_amount,
                'calculation_period' => $profile->calculation_period,
            ];
        });

        return response()->json([
            'bonusProfiles' => $formattedProfiles,
            'totalRecords' => $totalRecords,
            'currentPage' => $profiles->currentPage(),
        ]);
    }

    public function getAgents()
    {
        $users = User::where('role', 'agent')
            ->where('status', 'active')
            ->select('id', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'value' => $user->id,
                    'name' => $user->name,
                    'profile_photo' => $user->getFirstMediaUrl('profile_photo')
                ];
            });

        return response()->json([
            'users' => $users,
        ]);
    }

    public function createBonusProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent' => ['required'],
            'sales_calculation_mode' => ['required'],
            'sales_category' => ['required'],
            'target_amount' => ['required'],
            'bonus' => ['required'],
            'bonus_calculation_threshold' => ['required'],
            'bonus_calculation_period' => ['required'],
        ])->setAttributeNames([
            'agent' => trans('public.agent'),
            'sales_calculation_mode' => trans('public.sales_calculation_mode'),
            'sales_category' => trans('public.sales_category'),
            'target_amount' => trans('public.set_target_amount'),
            'bonus' => trans('public.bonus'),
            'bonus_calculation_threshold' => trans('public.bonus_calculation_threshold'),
            'bonus_calculation_period' => trans('public.bonus_calculation_period'),
        ]);
        $validator->validate();

        $billboard_profile = BillboardProfile::create([
            'user_id' => $request->agent['value'],
            'sales_calculation_mode' => $request->sales_calculation_mode,
            'sales_category' => $request->sales_category,
            'target_amount' => $request->target_amount,
            'bonus_rate' => $request->bonus,
            'bonus_calculation_threshold' => $request->bonus_calculation_threshold,
            'calculation_period' => $request->bonus_calculation_period,
            'edited_by' => \Auth::id()
        ]);

        switch ($billboard_profile->calculation_period) {
            case 'every_sunday':
                $nextPayout = Carbon::now()->next(Carbon::SUNDAY)->startOfDay();
                $billboard_profile->update([
                    'next_payout_at' => $nextPayout
                ]);
                break;

            case 'every_second_sunday':
                $nextPayout = Carbon::now()->next(Carbon::SUNDAY)->addWeek()->startOfDay();
                $billboard_profile->update([
                    'next_payout_at' => $nextPayout
                ]);
                break;

            case 'first_sunday_of_every_month':
                $nextPayout = Carbon::now()->startOfMonth()->addMonth()->firstOfMonth(Carbon::SUNDAY)->startOfDay();
                $billboard_profile->update([
                    'next_payout_at' => $nextPayout
                ]);
                break;

            default:
                return response()->json(['error' => 'Invalid period'], 400);
        }

        $user = User::find($billboard_profile->user_id);

        if (empty($user->bonus_wallet)) {
            Wallet::create([
                'user_id' => $user->id,
                'type' => 'bonus_wallet',
                'address' => str_replace('AID', 'BW', $user->id_number),
                'balance' => 0
            ]);
        }

        return redirect()->back()->with('toast', [
            "title" => trans('public.toast_create_bonus_profile_success'),
            "type" => "success"
        ]);
    }
}
