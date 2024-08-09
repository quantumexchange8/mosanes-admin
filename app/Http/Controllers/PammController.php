<?php

namespace App\Http\Controllers;

use App\Models\AssetMaster;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PammController extends Controller
{
    public function pamm_allocate()
    {
        return Inertia::render('PammAllocate/PammAllocate');
    }

    public function getMasters(Request $request)
    {
        $masters = AssetMaster::where('status', 'active')->get()->map(function($master) {
            return [
                'id' => $master->id,
                'asset_name' => $master->asset_name,
                'trader_name' => $master->trader_name,
                'total_investors' => $master->total_investors,
                'total_fund' => $master->total_fund,
                'minimum_investment' => $master->minimum_investment,
                'minimum_investment_period' => $master->minimum_investment_period,
                'performance_fee' => $master->performance_fee,
                'total_gain' => $master->total_gain,
                'monthly_gain' => $master->monthly_gain,
                'latest_profit' => $master->latest_profit,
            ];
        });

        return response()->json([
            'masters' => $masters
        ]);
    }
}