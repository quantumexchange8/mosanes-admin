<?php

namespace App\Http\Controllers;

use App\Models\AssetMaster;
use App\Models\AssetMasterProfitDistribution;
use App\Models\ForumPost;
use Inertia\Inertia;
use App\Models\AccountType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TradingAccount;
use App\Services\CTraderService;
use App\Models\TradeRebateSummary;
use App\Services\DropdownOptionService;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\Group;
use App\Models\GroupHasUser;
use App\Models\TradeLotSizeVolume;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard/Dashboard', [
            'postCounts' => ForumPost::count(),
        ]);
    }

    public function getPendingCounts()
    {
        $pendingWithdrawals = Transaction::whereNot('category', 'bonus_wallet')
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'processing')
            ->count();

        $pendingBonusWithdrawal = Transaction::where('category', 'bonus_wallet')
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'processing')
            ->count();

        $pendingPammAllocate = 0;

        $activeMasters = AssetMaster::where('status', 'active')->get();

        foreach ($activeMasters as $master) {
            $profitDistributionCount = AssetMasterProfitDistribution::where('asset_master_id', $master->id)
                ->whereDate('profit_distribution_date', '>=', today())
                ->count();

            if ($profitDistributionCount <= 3) {
                $pendingPammAllocate += 1;
            }
        }

        return response()->json([
            'pendingWithdrawals' => $pendingWithdrawals,
            'pendingPammAllocate' => $pendingPammAllocate,
            'pendingBonusWithdrawal' => $pendingBonusWithdrawal,
        ]);
    }

    public function getAccountData()
    {
        $from = '2024-01-01T00:00:00.000';
        $to = now()->format('Y-m-d\TH:i:s.v');

        // Standard Account and Premium Account group IDs
        $groupIds = AccountType::whereNotNull('account_group_id')
            ->pluck('account_group_id')
            ->toArray();

        foreach ($groupIds as $groupId) {
            // Fetch data for each group ID
            $response = (new CTraderService)->getMultipleTraders($from, $to, $groupId);

            // Find the corresponding AccountType model
            $accountType = AccountType::where('account_group_id', $groupId)->first();

            // Initialize or reset group balance and equity
            $groupBalance = 0;
            $groupEquity = 0;

            $meta_logins = TradingAccount::where('account_type_id', $accountType->id)->pluck('meta_login')->toArray();

            // Assuming the response is an associative array with a 'trader' key
            if (isset($response['trader']) && is_array($response['trader'])) {
                foreach ($response['trader'] as $trader) {
                    if (in_array($trader['login'], $meta_logins)) {
                        // Determine the divisor based on moneyDigits
                        $moneyDigits = isset($trader['moneyDigits']) ? (int)$trader['moneyDigits'] : 0;
                        $divisor = $moneyDigits > 0 ? pow(10, $moneyDigits) : 1; // 10^moneyDigits

                        // Adjust balance and equity based on the divisor
                        $groupBalance += $trader['balance'] / $divisor;
                        $groupEquity += $trader['equity'] / $divisor;
                    }
                }

                // Update account group balance and equity
                $accountType->account_group_balance = $groupBalance;
                $accountType->account_group_equity = $groupEquity;
                $accountType->save();
            }
        }

        // Recalculate total balance and equity from the updated account types
        $totalBalance = AccountType::sum('account_group_balance');
        $totalEquity = AccountType::sum('account_group_equity');

        // Return the total balance and total equity as a JSON response
        return response()->json([
            'totalBalance' => $totalBalance,
            'totalEquity' => $totalEquity,
        ]);
    }

    public function getPendingData()
    {
        $pendingWithdrawals = Transaction::whereNot('category', 'bonus_wallet')
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'processing');
        $pendingBonus = Transaction::where('category', 'bonus_wallet')
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'processing');
        
        return response()->json([
            'pendingWithdrawal' => $pendingWithdrawals->sum('transaction_amount'),
            'pendingWithdrawalCounts' => $pendingWithdrawals->count(),
            'pendingBonus' => $pendingBonus->sum('transaction_amount'),
            'pendingBonusCount' => $pendingBonus->count(),
        ]);
    }

    public function getAssetData(Request $request)
    {
        // Get the month from the request, default to the current month if not provided
        $monthYear = $request->query('month', date('m/Y'));
        [$month, $year] = explode('/', $monthYear);

        // Calculate total deposits
        $totalDeposit = Transaction::where('status', 'successful')
            ->whereIn('transaction_type', ['deposit', 'balance_in'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('transaction_amount');

        // Calculate total withdrawals
        $totalWithdrawal = Transaction::where('status', 'successful')
            ->whereIn('transaction_type', ['withdrawal', 'balance_out'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('transaction_amount');

        // Calculate total rebate payouts
        $totalRebatePayout = TradeRebateSummary::where('t_status', 'Approved')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get()
            ->map(function ($record) {
                // Multiply volume by rebate for each record
                return $record->volume * $record->rebate;
            })
            ->sum(); // Sum up all the calculated values

        return response()->json([
            'totalDeposit' => $totalDeposit,
            'totalWithdrawal' => $totalWithdrawal,
            'totalRebatePayout' => $totalRebatePayout,
        ]);
    }

    public function getDashboardData()
    {
        $total_deposit = Transaction::whereIn('transaction_type', ['deposit', 'rebate_in', 'balance_in', 'credit_in'])
            ->where('status', 'successful')
            ->sum('transaction_amount');
    
        $total_withdrawal = Transaction::whereIn('transaction_type', ['withdrawal', 'rebate_out', 'balance_out', 'credit_out'])
            ->where('status', 'successful')
            ->sum('amount');
        
        $total_agent = User::where('role', 'agent')->count();

        $total_member = User::where('role', 'member')->count();

        $today_deposit = Transaction::whereIn('transaction_type', ['deposit', 'rebate_in', 'balance_in', 'credit_in'])
        ->where('status', 'successful')
        ->whereDate('created_at', today())
        ->sum('transaction_amount');

        $today_withdrawal = Transaction::whereIn('transaction_type', ['withdrawal', 'rebate_out', 'balance_out', 'credit_out'])
            ->where('status', 'successful')
            ->whereDate('created_at', today())
            ->sum('amount');

        $today_agent = User::where('role', 'agent')->whereDate('created_at', today())->count();

        $today_member = User::where('role', 'member')->whereDate('created_at', today())->count();

        return response()->json([
            'totalDeposit' => $total_deposit,
            'totalWithdrawal' => $total_withdrawal,
            'totalAgent' => $total_agent,
            'totalMember' => $total_member,
            'todayDeposit' => $today_deposit,
            'todayWithdrawal' => $today_withdrawal,    
            'todayAgent' => $today_agent,
            'todayMember' => $today_member,
        ]);
    }

    public function getTradeLotVolume(Request $request)
    {
        // Get the selected month (in format "m/Y")
        $monthYear = $request->input('selectedMonth');
    
        // Parse the month/year string into a Carbon date
        $carbonDate = Carbon::createFromFormat('m/Y', $monthYear);
    
        // Get the year and month as integers
        $year = $carbonDate->year;
        $month = $carbonDate->month;
    
        // Check if any record exists where tlv_day = 0
        $hasSummaryRecord = TradeLotSizeVolume::where('tlv_year', $year)
                                              ->where('tlv_month', $month)
                                              ->where('tlv_day', 0)
                                              ->exists();
    
        if ($hasSummaryRecord) {
            // Use the summary record where tlv_day = 0
            $totalTradeLots = TradeLotSizeVolume::where('tlv_year', $year)
                                                ->where('tlv_month', $month)
                                                ->where('tlv_day', 0)
                                                ->sum('tlv_lotsize');
    
            $totalVolume = TradeLotSizeVolume::where('tlv_year', $year)
                                             ->where('tlv_month', $month)
                                             ->where('tlv_day', 0)
                                             ->sum('tlv_volume_usd');
        } else {
            // No summary record for this month, sum all available days
            $totalTradeLots = TradeLotSizeVolume::where('tlv_year', $year)
                                                ->where('tlv_month', $month)
                                                ->sum('tlv_lotsize');
    
            $totalVolume = TradeLotSizeVolume::where('tlv_year', $year)
                                             ->where('tlv_month', $month)
                                             ->sum('tlv_volume_usd');
        }
    
        // Return the total trade lots and volume as a JSON response
        return response()->json([
            'totalTradeLots' => $totalTradeLots,
            'totalVolume' => $totalVolume,
        ]);
    }
    
    public function getGroupsData(Request $request)
    {
        
        // Get the selected month (in format "m/Y")
        $monthYear = $request->input('selectedMonth');
        
        // Parse the month/year string into a Carbon date
        $carbonDate = Carbon::createFromFormat('m/Y', $monthYear);
        
        // Get the year and month as integers
        $year = $carbonDate->year;
        $month = $carbonDate->month;
        
        // Retrieve all groups and their related data
        $groups = Group::all()->map(function ($group) use ($year, $month) {
            // Get all user ids in the group
            $groupUserIds = GroupHasUser::where('group_id', $group->id)
                ->pluck('user_id')
                ->toArray();
    
            // Calculate total deposit for the group (filtered by month and year)
            $total_deposit = Transaction::whereIn('user_id', $groupUserIds)
                ->whereYear('approved_at', $year)
                ->whereMonth('approved_at', $month)
                ->whereIn('transaction_type', ['deposit', 'balance_in', 'rebate_in'])
                ->where('status', 'successful')
                ->sum('transaction_amount');
    
            // Calculate total withdrawal for the group (filtered by month and year)
            $total_withdrawal = Transaction::whereIn('user_id', $groupUserIds)
                ->whereYear('approved_at', $year)
                ->whereMonth('approved_at', $month)
                ->whereIn('transaction_type', ['withdrawal', 'balance_out', 'rebate_out'])
                ->where('status', 'successful')
                ->sum('transaction_amount');
    
            // Calculate total adjustment in for the group (filtered by month and year)
            $total_adjustment_in = Transaction::whereIn('user_id', $groupUserIds)
                ->whereYear('approved_at', $year)
                ->whereMonth('approved_at', $month)
                ->whereIn('transaction_type', ['balance_in', 'rebate_in'])
                ->where('status', 'successful')
                ->sum('transaction_amount');
        
            // Calculate total adjustment out for the group (filtered by month and year)
            $total_adjustment_out = Transaction::whereIn('user_id', $groupUserIds)
                ->whereYear('approved_at', $year)
                ->whereMonth('approved_at', $month)
                ->whereIn('transaction_type', ['balance_out', 'rebate_out'])
                ->where('status', 'successful')
                ->sum('transaction_amount');
    
            // Calculate the net balance for the group
            $transaction_fee_charges = $group->fee_charges > 0 ? $total_deposit / $group->fee_charges : 0;
            $net_balance = $total_deposit - $transaction_fee_charges - $total_withdrawal;
    
            // Calculate account balance and equity
            $groupIds = AccountType::whereNotNull('account_group_id')
                ->pluck('account_group_id')
                ->toArray();
    
            $groupBalance = 0;
            $groupEquity = 0;
    
            foreach ($groupIds as $groupId) {
                $startDateFormatted = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d\TH:i:s.v');
                $endDateFormatted = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d\TH:i:s.v');
                    
                $response = (new CTraderService)->getMultipleTraders($startDateFormatted, $endDateFormatted, $groupId);
    
                $accountType = AccountType::where('account_group_id', $groupId)->first();
    
                $meta_logins = TradingAccount::where('account_type_id', $accountType->id)
                    ->whereIn('user_id', $groupUserIds)
                    ->pluck('meta_login')
                    ->toArray();
    
                if (isset($response['trader']) && is_array($response['trader'])) {
                    foreach ($response['trader'] as $trader) {
                        if (in_array($trader['login'], $meta_logins)) {
                            $moneyDigits = isset($trader['moneyDigits']) ? (int)$trader['moneyDigits'] : 0;
                            $divisor = $moneyDigits > 0 ? pow(10, $moneyDigits) : 1;
    
                            $groupBalance += $trader['balance'] / $divisor;
                            $groupEquity += $trader['equity'] / $divisor;
                        }
                    }
                }
            }
    
            // Return the group data
            return [
                'id' => $group->id,
                'name' => $group->name,
                'fee_charges' => $group->fee_charges,
                'color' => $group->color,
                'leader_name' => $group->leader->name,
                'leader_email' => $group->leader->email,
                'profile_photo' => $group->leader->getFirstMediaUrl('profile_photo'),
                'member_count' => $group->group_has_user->count(),
                'deposit' => $total_deposit,
                'withdrawal' => $total_withdrawal,
                'transaction_fee_charges' => $transaction_fee_charges,
                'net_balance' => $net_balance,
                'adjustment_in' => $total_adjustment_in,
                'adjustment_out' => $total_adjustment_out,
                'account_balance' => $groupBalance,
                'account_equity' => $groupEquity,
            ];
        });
    
        return response()->json([
            'groups' => $groups,
        ]);
    }
}
