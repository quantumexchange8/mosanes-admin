<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Inertia\Inertia;

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
}
