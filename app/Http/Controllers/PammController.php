<?php

namespace App\Http\Controllers;

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
                'status' => $master->status,
                'created_at' => $master->created_at,
                'visible_to' => $master->type,
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
        // Fetch all active masters
        $masters = AssetMaster::where('status', 'active')->get();

        // Calculate totals for the current period
        $totalAsset = $masters->sum('total_fund');
        $totalInvestors = $masters->sum('total_investors');

        // Define date range for last month
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // Filter masters created in the last month
        $lastMonthMasters = $masters->filter(function ($master) use ($startOfLastMonth, $endOfLastMonth) {
            return $master->created_at->between($startOfLastMonth, $endOfLastMonth);
        });

        // Calculate totals for last month
        $lastMonthTotalAsset = $lastMonthMasters->sum('total_fund');
        $lastMonthTotalInvestors = $lastMonthMasters->sum('total_investors');

        // Calculate comparisons
        $totalAssetComparison = $lastMonthTotalAsset > 0
            ? (($totalAsset - $lastMonthTotalAsset) / $lastMonthTotalAsset) * 100
            : ($totalAsset > 0 ? 100 : 0);

        $investorComparison = $lastMonthTotalInvestors > 0
            ? (($totalInvestors - $lastMonthTotalInvestors) / $lastMonthTotalInvestors) * 100
            : ($totalInvestors > 0 ? 100 : 0);

        // Get and format top 3 masters by total fund
        $topThreeMasters = $masters->sortByDesc('total_fund')->take(3)->map(function($master) {
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
                'status' => $master->status,
                'created_at' => $master->created_at,
                'visible_to' => $master->type,
            ];
        });

        return response()->json([
            'totalAsset' => $totalAsset,
            'totalAssetComparison' => $totalAssetComparison,
            'currentInvestor' => $totalInvestors,
            'investorComparision' => $investorComparison,
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
            'created_date' => ['required'],
            'groups' => ['required'],
            'total_investors' => ['required', 'integer'],
            'total_fund' => ['required', 'numeric'],
        ];

        $attributes = [
            'pamm_name'=> trans('public.pamm_name'),
            'trader_name'=> trans('public.trader_name'),
            'created_date'=> trans('public.created_date'),
            'groups'=> trans('public.group'),
            'total_investors'=> trans('public.total_investors'),
            'total_fund'=> trans('public.total_fund'),
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($attributes);

        if ($request->step == 1) {
            // $validator->validate();
        } elseif ($request->step == 2) {
            $additionalRules = [
                'min_investment' => ['required', 'numeric'],
                'min_investment_period' => ['required', 'integer'],
                'performance_fee' => ['required', 'numeric'],
                'total_gain' => ['required', 'numeric'],
                'monthly_gain' => ['required', 'numeric'],
                'latest' => ['required', 'numeric'],
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
            // $validator->validate();
        }

        return back();
    }

    public function create_asset_master(Request $request)
    {
        // Define validation rules and attributes
        $rules = [
            'pamm_name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'trader_name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'created_date' => ['required', 'date'],
            'groups' => ['required'],
            'total_investors' => ['required', 'integer'],
            'total_fund' => ['required', 'numeric'],
            'min_investment' => ['required', 'numeric'],
            'min_investment_period' => ['required', 'integer'],
            'performance_fee' => ['required', 'numeric'],
            'total_gain' => ['required', 'numeric'],
            'monthly_gain' => ['required', 'numeric'],
            'latest' => ['required', 'numeric'],
            'expected_gain' => ['required', 'numeric'],
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
            'performance_fee' => trans('public.performance_fee'),
            'total_gain' => trans('public.total_gain'),
            'monthly_gain' => trans('public.monthly_gain'),
            'latest' => trans('public.latest'),
            'expected_gain'=> trans('public.expected_gain'),
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($attributes);
        // $validator->validate();

        // Determine the value of $visible based on the groups field
        $groups = $request->input('groups');

        if (in_array('public', $groups)) {
            $visible = 'public';
        } else {
            // Ensure $groups is an array
            $groupArray = is_array($groups) ? $groups : [];
            
            // Fetch groups with IDs in the $groupArray
            $groupsData = Group::whereIn('id', $groupArray)->get();
        }
        
        try {
            // AssetMaster::create([
            //     'asset_name' => $request->pamm_name,
            //     'trader_name' => $request->trader_name,
            //     'category' => 'pamm',
            //     'type' => $visible == 'public' ? 'public' : null,
            //     'started_at' => $request->started_at,
            //     'total_investors' => $request->total_investors,
            //     'total_fund' => $request->total_fund,
            //     'min_investment' => $request->min_investment,
            //     'min_investment_period' => $request->min_investment_period,
            //     'performance_fee' => $request->performance_fee,
            //     'total_gain' => $request->total_gain,
            //     'monthly_gain' => $request->monthly_gain,
            //     'latest' => $request->latest,
            //     'profit_generation_mode' => $request->profit_generation_mode,
            //     'expected_gain_profit' => $request->expected_gain,
            //     'status' => 'active',
            //     'edited_by' => Auth::id(),
            // ]);

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

}
