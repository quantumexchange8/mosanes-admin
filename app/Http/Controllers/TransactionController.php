<?php

namespace App\Http\Controllers;

use App\Models\SymbolGroup;
use Inertia\Inertia;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\TradeRebateSummary;
use App\Services\DropdownOptionService;

class TransactionController extends Controller
{
    public function listing()
    {
        return Inertia::render('Transaction/Transaction');
    }

    public function getTransactionListingData(Request $request)
    {
        $type = $request->query('type');
        $selectedMonths = $request->query('selectedMonths'); // Get selectedMonths as a comma-separated string
    
        // Convert the comma-separated string to an array
        $selectedMonthsArray = !empty($selectedMonths) ? explode(',', $selectedMonths) : [];
    
        // Define common fields
        $commonFields = [
            'id',
            'user_id',
            'category',
            'transaction_type',
            'transaction_number',
            'amount',
            'transaction_charges',
            'transaction_amount',
            'status',
            'remarks',
            'created_at',
        ];
    
        if (empty($selectedMonthsArray)) {
            // If selectedMonths is empty, return an empty result
            return response()->json([
                'transactions' => [],
            ]);
        }
    
        if ($type === 'payout') {
            $query = TradeRebateSummary::with('user');

            // Apply monthly filters
            $query->where(function ($query) use ($selectedMonthsArray) {
                foreach ($selectedMonthsArray as $range) {
                    [$month, $year] = explode('/', $range);
                    $startDate = "$year-$month-01";
                    $endDate = date("Y-m-t", strtotime($startDate)); // Last day of the month
                    $query->orWhereBetween('created_at', [$startDate, $endDate]);
                }
            });
            
            // Get startDate and endDate only if type is 'payout'
            $startDate = $request->query('startDate');
            $endDate = $request->query('endDate');
            
            // Apply date filter based on availability of startDate and/or endDate
            if ($startDate && $endDate) {
                // Both startDate and endDate are provided
                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            } elseif ($startDate) {
                // Only startDate is provided
                $query->whereDate('created_at', '>=', $startDate);
            } elseif ($endDate) {
                // Only endDate is provided
                $query->whereDate('created_at', '<=', $endDate);
            }
            
            // Fetch all symbol groups from the database
            $allSymbolGroups = SymbolGroup::pluck('display', 'id')->toArray();

            // Initialize the final data collection
            $finalData = collect();

            // Fetch summarized data
            $summarizedData = TradeRebateSummary::with('user')
                ->selectRaw('user_id, DATE(created_at) as date, SUM(volume) as volume, SUM(rebate) as rebate')
                ->groupBy('user_id', 'date')
                ->latest('date')
                ->get();

            // Loop through each summarized record to get details and flatten data
            foreach ($summarizedData as $summary) {
                // Retrieve detailed records for each user_id, date
                $details = TradeRebateSummary::where('user_id', $summary->user_id)
                    ->whereDate('created_at', $summary->date)
                    ->selectRaw('symbol_group, SUM(volume) as volume, SUM(rebate) as rebate')
                    ->groupBy('symbol_group')
                    ->get()
                    ->keyBy('symbol_group')
                    ->map(function ($item) {
                        return [
                            'id' => $item->symbol_group,
                            'volume' => $item->volume,
                            'rebate' => $item->rebate,
                        ];
                    });

                // Ensure all symbol groups are included in the details and sort them by the order of $allSymbolGroups
                $details = collect($allSymbolGroups)->mapWithKeys(function ($name, $id) use ($details) {
                    return [
                        $id => $details->get($id, [
                            'id' => $id,
                            'volume' => 0,
                            'rebate' => 0,
                        ])
                    ];
                })->map(function ($item) use ($allSymbolGroups) {
                    return [
                        'id' => $item['id'],
                        'symbol_group' => $allSymbolGroups[$item['id']],
                        'volume' => $item['volume'],
                        'rebate' => $item['rebate'],
                    ];
                });

                // Add data with details to finalData
                $finalData->push([
                    'user_id' => $summary->user_id,
                    'name' => $summary->user->name,
                    'email' => $summary->user->email,
                    'volume' => $summary->volume,
                    'rebate' => $summary->rebate,
                    'created_at' => $summary->date,
                    'details' => $details->sortKeys()->values(),
                ]);
            }

            // Assign the finalData to $data
            $data = $finalData;
    
        } else {
            $query = Transaction::with('user', 'from_wallet', 'to_wallet');
    
            // Apply filtering for each selected month-year pair
            if (!empty($selectedMonthsArray)) {
                $query->where(function ($q) use ($selectedMonthsArray) {
                    foreach ($selectedMonthsArray as $range) {
                        [$month, $year] = explode('/', $range);
                        $startDate = "$year-$month-01";
                        $endDate = date("Y-m-t", strtotime($startDate)); // Last day of the month

                        // Add a condition to match transactions for this specific month-year
                        $q->orWhereBetween('created_at', [$startDate, $endDate]);
                    }
                });
            }
    
            // Filter by transaction type
            if ($type) {
                if ($type === 'transfer') {
                    $query->where(function ($q) {
                        $q->where('transaction_type', 'transfer_to_account')
                        ->orWhere('transaction_type', 'account_to_account');
                    });
                } else {
                    $query->where('transaction_type', $type);
                }
            }
    
            // Fetch data
            $data = $query->latest()->get()->map(function ($transaction) use ($commonFields, $type) {
                // Initialize result array with common fields
                $result = $transaction->only($commonFields);
    
                // Add common user fields
                $result['name'] = $transaction->user ? $transaction->user->name : null;
                $result['email'] = $transaction->user ? $transaction->user->email : null;
                $result['role'] = $transaction->user ? $transaction->user->role : null;
    
                // Add type-specific fields
                if ($type === 'deposit') {
                    $result['from_wallet_address'] = $transaction->from_wallet_address;
                    $result['to_wallet_address'] = $transaction->to_wallet_address;
                    $result['to_meta_login'] = $transaction->to_meta_login;
                    $result['to_wallet_id'] = $transaction->to_wallet ? $transaction->to_wallet->id : null;
                    $result['to_wallet_name'] = $transaction->to_wallet ? $transaction->to_wallet->name : null;
                } elseif ($type === 'withdrawal') {
                    $result['to_wallet_address'] = $transaction->to_wallet_address;
                    $result['from_meta_login'] = $transaction->from_meta_login;
                    $result['from_wallet_id'] = $transaction->from_wallet ? $transaction->from_wallet->id : null;
                    $result['from_wallet_name'] = $transaction->from_wallet ? $transaction->from_wallet->name : null;
                    $result['to_wallet_id'] = $transaction->to_wallet ? $transaction->to_wallet->id : null;
                    $result['to_wallet_name'] = $transaction->to_wallet ? $transaction->to_wallet->name : null;
                    $result['approved_at'] = $transaction->approved_at;
                } elseif ($type === 'transfer') {
                    $result['from_meta_login'] = $transaction->from_meta_login;
                    $result['to_meta_login'] = $transaction->to_meta_login;
                    $result['from_wallet_id'] = $transaction->from_wallet ? $transaction->from_wallet->id : null;
                    $result['from_wallet_name'] = $transaction->from_wallet ? $transaction->from_wallet->name : null;
                    $result['to_wallet_id'] = $transaction->to_wallet ? $transaction->to_wallet->id : null;
                    $result['to_wallet_name'] = $transaction->to_wallet ? $transaction->to_wallet->name : null;
                }
    
                return $result;
            });
        }
    
        return response()->json([
            'transactions' => $data,
        ]);
    }

    public function getTransactionMonths()
    {
        $transactionMonths = (new DropdownOptionService())->getTransactionMonths();

        return response()->json($transactionMonths);
    }

}
