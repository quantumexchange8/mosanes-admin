<?php

namespace App\Http\Controllers;

use App\Models\AssetMasterProfitDistribution;
use App\Models\AssetMasterToGroup;
use App\Models\AssetSubscription;
use Inertia\Inertia;
use App\Models\Group;
use App\Models\AssetMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\DropdownOptionService;
use Illuminate\Support\Facades\Validator;

class PammController extends Controller
{
    public function pamm_allocate()
    {
        return Inertia::render('PammAllocate/PammAllocate');
    }

    public function getMasters(Request $request)
    {
        // fetch limit with default
        $limit = $request->input('limit', 12);

        // Fetch parameter from request
        $search = $request->input('search', '');
        $sortType = $request->input('sortType', '');
        $groups = $request->input('groups', '');
        $adminUser = $request->input('adminUser', '');
        $tag = $request->input('tag', '');
        $status = $request->input('status', '');

        // Fetch paginated masters
        $mastersQuery = AssetMaster::query();

        // Apply search parameter to multiple fields
        if (!empty($search)) {
            $mastersQuery->where(function($query) use ($search) {
                $query->where('trader_name', 'LIKE', "%$search%")
                    ->orWhere('asset_name', 'LIKE', "%$search%")
                    ->orWhere('total_investors', 'LIKE', "%$search%")
                    ->orWhere('total_fund', 'LIKE', "%$search%")
                    ->orWhere('performance_fee', 'LIKE', "%$search%")
                    ->orWhere('total_gain', 'LIKE', "%$search%")
                    ->orWhere('monthly_gain', 'LIKE', "%$search%")
                    ->orWhere('latest_profit', 'LIKE', "%$search%");
            });
        }

        // Apply sorting dynamically
        if (in_array($sortType, ['created_at', 'total_investors', 'total_fund'])) {
            $mastersQuery->orderBy($sortType, 'desc');
        }

        // // Apply groups filter
        // if (!empty($groups)) {
        //     dd($request->all());
        // }

        // // Apply adminUser filter
        // if (!empty($adminUser)) {
        //     dd($request->all());
        // }

        // // Apply tag filter
        // if (!empty($tag)) {
        //     dd($request->all());
        // }

        // Apply status filter
        if (!empty($status)) {
            $mastersQuery->where('status', $status);
        }

        // Get total count of masters
        $totalRecords = $mastersQuery->count();

        // Fetch paginated results
        $masters = $mastersQuery->paginate($limit);

        // Format masters
        $formattedMasters = $masters->map(function($master) {

            $group_names = null;
            $group_ids = $master->visible_to_groups()
                ->pluck('group_id')
                ->toArray();

            if ($master->type == 'private') {
                $groups = Group::whereIn('id', $group_ids)->get();

                $group_names = $groups->pluck('name')->implode(', ');
            }

            return [
                'id' => $master->id,
                'asset_name' => $master->asset_name,
                'trader_name' => $master->trader_name,
                'total_investors' => $master->asset_subscriptions()->where('status', 'ongoing')->count(),
                'total_fund' => $master ->asset_subscriptions()->where('status', 'ongoing')->sum('investment_amount'),
                'minimum_investment' => $master->minimum_investment,
                'minimum_investment_period' => $master->minimum_investment_period,
                'performance_fee' => $master->performance_fee,
                'total_gain' => $master->total_gain,
                'monthly_gain' => $master->monthly_gain,
                'latest_profit' => $master->latest_profit,
                'status' => $master->status,
                'created_at' => $master->created_at,
                'visible_to' => $master->type,
                'group_names' => $group_names,
                'asset_distribution_counts' => $master->asset_distributions()->count(),
                'total_likes_count' => $master->total_likes_count,
            ];
        });

        return response()->json([
            'masters' => $formattedMasters,
            'totalRecords' => $totalRecords,
            'currentPage' => $masters->currentPage(),
        ]);
    }

    public function getMetrics()
    {
        // current month
        $endOfMonth = Carbon::now()->endOfMonth();

        // last month
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $asset_subscription_query = AssetSubscription::where('status', 'ongoing');

        // current month assets
        $current_month_assets = (clone $asset_subscription_query)
            ->where('status', 'ongoing')
            ->whereDate('created_at', '<=', $endOfMonth)
            ->sum('investment_amount');

        // current month investors
        $current_month_investors = (clone $asset_subscription_query)
            ->where('status', 'ongoing')
            ->whereDate('created_at', '<=', $endOfMonth)
            ->count();

        // last month assets
        $last_month_assets = (clone $asset_subscription_query)
            ->where('status', 'ongoing')
            ->whereDate('created_at', '<=', $endOfLastMonth)
            ->sum('investment_amount');

        // last month investors
        $last_month_investors = (clone $asset_subscription_query)
            ->where('status', 'ongoing')
            ->whereDate('created_at', '<=', $endOfLastMonth)
            ->count();

        // comparison % of assets vs last month
        $last_month_asset_comparison = $last_month_assets > 0
            ? (($current_month_assets - $last_month_assets) / $last_month_assets) * 100
            : ($current_month_assets > 0 ? 100 : 0);

        // comparison % of investors vs last month
        $last_month_investor_comparison = $current_month_investors - $last_month_investors;

        // Get and format top 3 masters by total fund
        $topThreeMasters = AssetMaster::get()
            ->map(function ($master) use ($endOfMonth) {
                $asset_subscriptions_fund = AssetSubscription::where('status', 'ongoing')
                    ->where('asset_master_id', $master->id)
                    ->whereDate('created_at', '<=', $endOfMonth)
                    ->sum('investment_amount');

                return [
                    'id' => $master->id,
                    'asset_name' => $master->asset_name,
                    'trader_name' => $master->trader_name,
                    'total_fund' => $asset_subscriptions_fund,
                    'minimum_investment' => $master->minimum_investment,
                    'minimum_investment_period' => $master->minimum_investment_period,
                    'performance_fee' => $master->performance_fee,
                    'total_gain' => $master->total_gain,
                    'monthly_gain' => $master->monthly_gain,
                    'latest_profit' => $master->latest_profit,
                    'status' => $master->status,
                    'created_at' => $master->created_at,
                    'visible_to' => $master->type,
                ];
            })
            ->sortByDesc('total_fund')
            ->take(3)
            ->values();

        return response()->json([
            'currentAssets' => $current_month_assets,
            'lastMonthAssetComparison' => $last_month_asset_comparison,
            'currentInvestors' => $current_month_investors,
            'lastMonthInvestorComparison' => $last_month_investor_comparison,
            'topThreeMasters' => $topThreeMasters,
        ]);
    }

    public function getOptions()
    {
        return response()->json([
            'groupsOptions' => (new DropdownOptionService())->getGroups(),
        ]);
    }

    public function getProfitLoss(Request $request)
    {
        dd($request->all());
        // return response()->json([
        //     'profit' => $profit,
        //     'loss' => $loss,
        // ]);
    }

    public function validateStep(Request $request)
    {
        $rules = [
            'pamm_name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'trader_name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'started_at' => ['required'],
            'groups' => ['required'],
            'total_investors' => ['required', 'integer'],
            'total_fund' => ['required', 'numeric'],
        ];

        $attributes = [
            'pamm_name'=> trans('public.pamm_name'),
            'trader_name'=> trans('public.trader_name'),
            'started_at'=> trans('public.created_date'),
            'groups'=> trans('public.group'),
            'total_investors'=> trans('public.total_investors'),
            'total_fund'=> trans('public.total_fund'),
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($attributes);

        if ($request->step == 1) {
             $validator->validate();
        } elseif ($request->step == 2) {
            $additionalRules = [
                'min_investment' => ['required'],
                'min_investment_period' => ['required'],
                'performance_fee' => ['required'],
                'total_gain' => ['required'],
                'monthly_gain' => ['required'],
                'latest' => ['required'],
            ];
            $rules = array_merge($rules, $additionalRules);

            $additionalAttributes = [
                'min_investment'=> trans('public.min_investment'),
                'min_investment_period'=> trans('public.min_investment_period'),
                'performance_fee'=> trans('public.performance_fee'),
                'total_gain'=> trans('public.total_gain'),
                'monthly_gain'=> trans('public.monthly_gain'),
                'latest'=> trans('public.latest'),
            ];
            $attributes = array_merge($attributes, $additionalAttributes);

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($attributes);
            $validator->validate();
        }

        return back();
    }

    public function create_asset_master(Request $request)
    {
        // Define validation rules and attributes
        $rules = [
//            'pamm_name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
//            'trader_name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
//            'created_date' => ['required', 'date'],
//            'groups' => ['required'],
//            'total_investors' => ['required', 'integer'],
//            'total_fund' => ['required', 'numeric'],
//            'min_investment' => ['required', 'numeric'],
//            'min_investment_period' => ['required', 'integer'],
//            'performance_fee' => ['required', 'numeric'],
//            'total_gain' => ['required', 'numeric'],
//            'monthly_gain' => ['required', 'numeric'],
//            'latest' => ['required', 'numeric'],
            'expected_gain' => ['nullable', 'numeric'],
        ];

        $attributes = [
//            'pamm_name' => trans('public.pamm_name'),
//            'trader_name' => trans('public.trader_name'),
//            'created_date' => trans('public.created_date'),
//            'groups' => trans('public.group'),
//            'total_investors' => trans('public.total_investors'),
//            'total_fund' => trans('public.total_fund'),
//            'min_investment' => trans('public.min_investment'),
//            'min_investment_period' => trans('public.min_investment_period'),
//            'performance_fee' => trans('public.performance_fee'),
//            'total_gain' => trans('public.total_gain'),
//            'monthly_gain' => trans('public.monthly_gain'),
//            'latest' => trans('public.latest'),
            'expected_gain'=> trans('public.expected_gain'),
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($attributes);
        $validator->validate();

        // Determine the value of $visible based on the groups field
        $groups = $request->input('groups');

        $groupsDatas = [];
        if (in_array('public', $groups)) {
            $visible = 'public';
        } else {
            $visible = 'private';
            // Ensure $groups is an array
            $groupArray = is_array($groups) ? $groups : [];

            // Fetch groups with IDs in the $groupArray
            $groupsDatas = Group::whereIn('id', $groupArray)->get();
        }

        try {
             $asset_master = AssetMaster::create([
                 'asset_name' => $request->pamm_name,
                 'trader_name' => $request->trader_name,
                 'category' => 'pamm',
                 'type' => $visible,
                 'started_at' => $request->started_at,
                 'total_investors' => $request->total_investors,
                 'total_fund' => $request->total_fund,
                 'minimum_investment' => $request->min_investment,
                 'minimum_investment_period' => $request->min_investment_period,
                 'performance_fee' => $request->performance_fee,
                 'total_gain' => $request->total_gain,
                 'monthly_gain' => $request->monthly_gain,
                 'latest_profit' => $request->latest,
                 'profit_generation_mode' => $request->profit_generation_mode,
                 'expected_gain_profit' => $request->expected_gain,
                 'status' => 'active',
                 'edited_by' => Auth::id(),
             ]);

             if ($asset_master->type == 'private') {
                 foreach ($groupsDatas as $group) {
                     AssetMasterToGroup::create([
                         'asset_master_id' => $asset_master->id,
                         'group_id' => $group->id,
                     ]);
                 }
             }

             $daily_profits = $request->daily_profits;
             if ($daily_profits) {
                 foreach ($daily_profits as $daily_profit) {
                     $date = \DateTime::createFromFormat('d/m', $daily_profit['date']);

                     if ($date) {
                         $date->setDate(date('Y'), $date->format('m'), $date->format('d'));

                         AssetMasterProfitDistribution::create([
                             'asset_master_id' => $asset_master->id,
                             'profit_distribution_date' => $date->format('Y-m-d'),
                             'profit_distribution_percent' => $daily_profit['daily_profit'],
                         ]);
                     }
                 }
             }

            // Redirect with success message
            return redirect()->back()->with('toast', [
                "title" => "You've successfully created a new asset master!",
                "type" => "success"
            ]);
        } catch (\Exception $e) {
            // Log the exception and show a generic error message
            Log::error('Error creating asset master: '.$e->getMessage());

            return redirect()->back()->with('toast', [
                'title' => 'There was an error creating the asset master.',
                'type' => 'error'
            ]);
        }
    }

    public function edit_asset_master(Request $request)
    {
        // Define validation rules and attributes
        $rules = [
            'pamm_name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'trader_name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'created_date' => ['required', 'date'],
            'groups' => ['required'],
            'total_investors' => ['nullable', 'integer'],
            'total_fund' => ['nullable', 'numeric'],
            'min_investment' => ['nullable', 'numeric'],
            'min_investment_period' => ['required', 'integer'],
            'profit_sharing' => ['nullable', 'numeric'],
        ];

        $attributes = [
            'pamm_name' => trans('public.pamm_name'),
            'trader_name' => trans('public.trader_name'),
            'created_date' => trans('public.created_date'),
            'groups' => trans('public.group'),
            'total_investors' => trans('public.total_investors'),
            'total_fund' => trans('public.total_fund'),
            'min_investment' => trans('public.min_investment'),
            'min_investment_period' => trans('public.min_investment_period'),
            'profit_sharing' => trans('public.profit_sharing'),
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($attributes);
        $validator->validate();

        // Determine the value of $visible based on the groups field
        $groups = $request->input('groups');

        if (in_array('public', $groups)) {
            $visible = 'public';
        } else {
            $visible = null;
            // Ensure $groups is an array
            $groupArray = is_array($groups) ? $groups : [];

            // Fetch groups with IDs in the $groupArray
            $groupsData = Group::whereIn('id', $groupArray)->get();
        }

        try {
            // Find the asset master by ID
            $assetMaster = AssetMaster::findOrFail($request->id);

            // Update the asset master record
            // $assetMaster->update([
            //     'asset_name' => $request->pamm_name,
            //     'trader_name' => $request->trader_name,
            //     'type' => $visible,
            //     'started_at' => $request->started_at,
            //     'total_investors' => $request->total_investors,
            //     'total_fund' => $request->total_fund,
            //     'min_investment' => $request->min_investment,
            //     'min_investment_period' => $request->min_investment_period,
            //     'edited_by' => Auth::id(),
            // ]);

            // Redirect with success message
            return redirect()->back()->with('toast', [
                "title" => "You've successfully updated the asset master!",
                "type" => "success"
            ]);
        } catch (\Exception $e) {
            // Log the exception and show a generic error message
            Log::error('Error updating asset master: '.$e->getMessage());

            return redirect()->back()->with('toast', [
                'title' => 'There was an error updating the asset master.',
                'type' => 'error'
            ]);
        }
    }


    public function upload_image(Request $request)
    {
        dd($request->all());
    }

    public function update_asset_master_status(Request $request)
    {
        $assetMaster = AssetMaster::find($request->id);

        $assetMaster->status = $assetMaster->status == 'active' ? 'inactive' : 'active';
        $assetMaster->save();

        return back()->with('toast', [
            'title' => $assetMaster->status == 'active' ? trans("public.toast_asset_master_show") : trans("public.toast_asset_master_hide"),
            'type' => 'success',
        ]);
    }

    public function disband(Request $request)
    {
        $assetMaster = AssetMaster::find($request->id);

        return back()->with('toast', [
            'title' => trans("public.toast_asset_master_disband"),
            'type' => 'success',
        ]);
    }

    public function updateLikeCounts(Request $request)
    {
        $assetMaster = AssetMaster::find($request->master_id);

        $assetMaster->total_likes_count += $request->likeCounts;
        $assetMaster->save();

        return back();
    }

    public function getJoiningPammAccountsData(Request $request)
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $query = AssetSubscription::where('asset_master_id', $request->asset_master_id)
            ->whereNot('status', 'revoked');

        if ($startDate && $endDate) {
            $start_date = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $end_date = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $joiningPammAccounts = $query
            ->latest()
            ->get()
            ->map(function ($item) {

                if ($item->status == 'ongoing') {
                    $displayStatus = $item->matured_at ? intval(now()->diffInDays($item->matured_at)) : null;
                } else {
                    $displayStatus = $item->status;
                }

                return [
                    'id' => $item->id,
                    'user_profile_photo' => $item->user->getFirstMediaUrl('profile_photo'),
                    'user_name' => $item->user->name,
                    'user_email' => $item->user->email,
                    'join_date' => $item->created_at,
                    'meta_login' => $item->meta_login,
                    'balance' => $item->investment_amount + $item->top_up_amount,
                    'status' => $item->status,
                    'remaining_days' => $displayStatus,
                    'investment_periods' => $item->investment_periods
                ];
            });

        return response()->json([
            'joiningPammAccounts' => $joiningPammAccounts,
            'totalInvestmentAmount' => $joiningPammAccounts->sum('balance'),
        ]);
    }
}
