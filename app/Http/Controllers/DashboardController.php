<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\AccountType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TradingAccount;
use App\Services\CTraderService;
use App\Models\TradeRebateSummary;
use App\Services\DropdownOptionService;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard/Dashboard');
    }

    public function getPendingCounts()
    {
        $pendingWithdrawals = Transaction::where('transaction_type', 'withdrawal')
            ->where('status', 'processing')
            ->count();

        return response()->json([
            'pendingCounts' => $pendingWithdrawals
        ]);
    }

    public function getOptions()
    {
        $transactionMonths = (new DropdownOptionService())->getTransactionMonths();

        return response()->json($transactionMonths);
    }

    public function getAccountData()
    {
        $from = '2024-01-01T00:00:00.000';
        $to = now()->format('Y-m-d\TH:i:s.v');
    
        // Standard Account and Premium Account group id
        $groupIds = [1047, 1048];
    
        $totalBalance = 0;
        $totalEquity = 0;
    
        foreach ($groupIds as $groupId) {
            // Fetch data for each group ID
            $response = (new CTraderService)->getMultipleTraders($from, $to, $groupId);
    
            // Assuming the response is an associative array with a 'trader' key
            if (isset($response['trader']) && is_array($response['trader'])) {
                foreach ($response['trader'] as $trader) {
                    $totalBalance += $trader['balance'];
                    $totalEquity += $trader['equity'];
                }
            }
        }
    
        // Return the total balance and total equity as a JSON response
        return response()->json([
            'totalBalance' => $totalBalance,
            'totalEquity' => $totalEquity,
        ]);
    }
                
    public function getPendingData()
    {

        $pendingWithdrawals = Transaction::where('transaction_type', 'withdrawal')->where('status', 'processing');

        return response()->json([
            'pendingAmount' => $pendingWithdrawals->sum('transaction_amount'),
            'pendingCounts' => $pendingWithdrawals->count(),
        ]);
    }

    public function getAssetData(Request $request)
    {
        // Get the month from the request, default to the current month if not provided
        $monthYear = $request->query('month', date('m/Y'));
        [$month, $year] = explode('/', $monthYear);
    
        // Calculate total deposits
        $totalDeposit = Transaction::where('status', 'successful')
            ->where('transaction_type', 'deposit')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('transaction_amount');
    
        // Calculate total withdrawals
        $totalWithdrawal = Transaction::where('status', 'successful')
            ->where('transaction_type', 'withdrawal')
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
    
}
