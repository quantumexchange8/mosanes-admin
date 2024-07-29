<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            $query = Transaction::with('user', 'from_wallet', 'to_wallet');
    
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

            // Convert date strings to YYYY-MM-DD format directly
            if ($startDate) {
                // Extract the date part from the string
                if (preg_match('/(\w{3} \w{3} \d{2} \d{4})/', $startDate, $matches)) {
                    $datePart = $matches[1]; // e.g., "Jul 10 2024"
                    $startDate = (new \DateTime($datePart))->format('Y-m-d');
                } else {
                    $startDate = null; // Handle error or invalid format
                }
            }

            if ($endDate) {
                // Extract the date part from the string
                if (preg_match('/(\w{3} \w{3} \d{2} \d{4})/', $endDate, $matches)) {
                    $datePart = $matches[1]; // e.g., "Jul 10 2024"
                    $endDate = (new \DateTime($datePart))->format('Y-m-d');
                } else {
                    $endDate = null; // Handle error or invalid format
                }
            }

            // Apply date filter based on availability of startDate and/or endDate
            if ($startDate && $endDate) {
                // Both startDate and endDate are provided
                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }
                                    
            $data = $query->latest()->get()->map(function ($payout) use ($commonFields) {
                return [
                    'id' => $payout->id,
                    'user_id' => $payout->user_id,
                    'amount' => $payout->amount,
                    'transaction_amount' => $payout->transaction_amount,
                    'date' => $payout->date,
                    'status' => $payout->status,
                    'name' => $payout->user ? $payout->user->name : null,
                    'email' => $payout->user ? $payout->user->email : null,
                    'role' => $payout->user ? $payout->user->role : null,
                    'created_at' => date_format($payout->created_at, 'Y-m-d'),
                ];
            });
    
        } else {
            $query = Transaction::with('user', 'from_wallet', 'to_wallet');
    
            // Apply filtering for each selected month-year pair
            foreach ($selectedMonthsArray as $range) {
                [$month, $year] = explode('/', $range);
                $startDate = "$year-$month-01";
                $endDate = date("Y-m-t", strtotime($startDate)); // Last day of the month

                // Add a condition to match transactions for this specific month-year
                $query->orWhereBetween('created_at', [$startDate, $endDate]);
            }
    
            // // Filter by transaction type
            // if ($type) {
            //     $query->where('transaction_type', $type);
            // }
    
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
