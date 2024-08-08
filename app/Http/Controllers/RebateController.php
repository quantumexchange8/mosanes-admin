<?php

namespace App\Http\Controllers;

use App\Models\RebateAllocation;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RebateController extends Controller
{
    public function rebate_allocate()
    {
        return Inertia::render('RebateAllocate/RebateAllocate');
    }

    public function getCompanyProfileData(Request $request)
    {
        $userId = 2;

        $company_profile = RebateAllocation::with(['user' => function ($query) {
            $query->withCount(['directChildren as direct_agent' => function ($q) {
                $q->where('role', 'agent');
            }]);
        }])
            ->where('user_id', $userId)
            ->where('account_type_id', $request->account_type_id)
            ->first();

        if (!$company_profile || !$company_profile->user) {
            return back()->with('toast', [
                'title' => 'Invalid Account Type',
                'type' => 'warning',
            ]);
        }

        $company_profile->user->group_agent = $this->getChildrenCount($userId);

        $levels = $this->getHierarchyLevels($company_profile->user, $company_profile->user->id);
        $company_profile->user->minimum_level = $levels['min'];
        $company_profile->user->maximum_level = $levels['max'];

        // Fetch rebate details
        $rebate_details = RebateAllocation::with('symbol_group:id,display')
            ->where('user_id', $userId)
            ->where('account_type_id', $request->account_type_id)
            ->get();

        return response()->json([
            'companyProfile' => $company_profile,
            'rebateDetails' => $rebate_details
        ]);
    }

    public function updateRebateAllocation(Request $request)
    {
        $ids = $request->id;
        $amounts = $request->amount;

        foreach ($ids as $index => $id) {
            RebateAllocation::find($id)->update([
                'amount' => $amounts[$index],
                'edited_by' => Auth::id()
            ]);
        }

        return redirect()->back()->with('toast', [
            'title' => trans('public.update_rebate_success_alert'),
            'type' => 'success'
        ]);
    }

    public function getRebateStructureData(Request $request)
    {
        // Retrieve rebate structure data based on account type ID and user ID
        $rebate_structure = RebateAllocation::with(['user:id,email,id_number,upline_id,hierarchyList'])
            ->where('account_type_id', $request->account_type_id)
            ->where('user_id', $request->user_id)
            ->get();

        // Get the upline user
        $upline = User::find($request->user_id);

        // Get the direct children of the upline user
        $users = $upline->directChildren;

        // Function to get all downline users recursively with the role 'agent'
        function getDownlineAgents($user)
        {
            $downline = collect();

            foreach ($user->directChildren as $child) {
                if ($child->role == 'agent') {
                    $downline->push([
                        'id' => $child->id,
                        'name' => $child->name,
                        'profile_pic' => $child->getFirstMediaUrl('profile_pic'),
                    ]);
                }
                $downline = $downline->merge(getDownlineAgents($child));
            }

            return $downline;
        }

        // Get all downline agents for each direct child
        $downline_agents = collect();
        foreach ($users as $user) {
            $downline_agents = $downline_agents->merge(getDownlineAgents($user));
        }

        return response()->json([
            'rebateStructures' => $rebate_structure,
            'users' => $users,
            'downlineAgents' => $downline_agents,
        ]);
    }


    private function getChildrenCount($user_id): int
    {
        return User::where('role', 'agent')
            ->where('hierarchyList', 'like', '%-' . $user_id . '-%')
            ->count();
    }

    private function getHierarchyLevels($upline, $user_id)
    {
        $users = User::whereIn('id', $upline->getChildrenIds())->get();
        $minLevel = PHP_INT_MAX;
        $maxLevel = PHP_INT_MIN;

        foreach ($users as $user) {
            $levels = explode('-', trim($user->hierarchyList, '-'));
            if (in_array($user_id, array_map('intval', $levels))) {
                $levelCount = count($levels);
                $minLevel = min($minLevel, $levelCount);
                $maxLevel = max($maxLevel, $levelCount);
            }
        }

        return [
            'min' => $minLevel == PHP_INT_MAX ? 0 : $minLevel,
            'max' => $maxLevel == PHP_INT_MIN ? 0 : $maxLevel
        ];
    }

    public function getAgents(Request $request)
    {
        $type_id = $request->type_id;

        //level 1 children
        $lv1_agents = User::where('upline_id', 2)->where('role', 'agent')
            ->get()->map(function($agent) {
                return [
                    'id' => $agent->id,
                    'profile_photo' => $agent->getFirstMediaUrl('profile_photo'),
                    'name' => $agent->name,
                    'hierarchy_list' => $agent->hierarchyList,
                    'upline_id' => $agent->upline_id,
                    'level' => 1,
                ];
            })->toArray();

        //level 1 children rebate
        $lv1_rebate = $this->getRebateAllocate($lv1_agents[0]['id'], $type_id);

        $agents_array = [];
        $lv1_data = [];

        // push lvl 1 agent & rebate
        array_push($lv1_data, $lv1_agents, $lv1_rebate);
        array_push($agents_array, $lv1_data);

        // children of first level 1 agent
        $children_ids = User::find($lv1_agents[0]['id'])->getChildrenIds();
        // dd($children_ids);
        $agents = User::whereIn('id', $children_ids)->where('role', 'agent')
            ->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
                    'name' => $user->name,
                    'hierarchy_list' => $user->hierarchyList,
                    'upline_id' => $user->upline_id,
                    'level' => $this->calculateLevel($user->hierarchyList),
                ];
            })
            ->groupBy('level')->toArray();

        // push lvl 2 and above agent & rebate into array 
        for ($i = 2; $i <= sizeof($agents) + 1; $i++) {
            $temp = [];
            $rebate = $this->getRebateAllocate($agents[$i][0]['id'], $type_id);

            array_push($temp, $agents[$i], $rebate);
            array_push($agents_array, $temp);
        }

        return response()->json($agents_array);
    }

    private function calculateLevel($hierarchyList)
    {
        if (is_null($hierarchyList) || $hierarchyList === '') {
            return 0;
        }

        $split = explode('-2-', $hierarchyList);
        return substr_count($split[1], '-') + 1;
    }

    private function getRebateAllocate($user_id, $type_id)
    {
        $rebate = User::find($user_id)->rebateAllocations()->where('account_type_id', $type_id)->get();

        $data = [
            'user_id' => $rebate[0]->user_id,
            'forex' => $rebate[0]->amount,
            'stocks' => $rebate[1]->amount,
            'indices' => $rebate[2]->amount,
            'commodities' => $rebate[3]->amount,
            'cryptocurrency' => $rebate[4]->amount,
        ];

        return $data;
    }

    public function changeAgents(Request $request)
    {
        // dd($request->all());
        $selected_agent_id = $request->id;
        $type_id = $request->type_id;
        $agents_array = [];

        if($request->level == 1) {
            $lv1_data = [];

            //level 1 children
            $lv1_agents = User::find(2)->directChildren()->where('role', 'agent')->get()
                ->map(function($agent) {
                    return [
                        'id' => $agent->id,
                        'profile_photo' => $agent->getFirstMediaUrl('profile_photo'),
                        'name' => $agent->name,
                        'hierarchy_list' => $agent->hierarchyList,
                        'upline_id' => $agent->upline_id,
                        'level' => 1,
                    ];
                })
                ->sortBy(fn($agent) => $agent['id'] != $selected_agent_id)
                ->toArray();

            // reindex
            $lv1_agents = array_values($lv1_agents);

            //level 1 children rebate
            $lv1_rebate = $this->getRebateAllocate($lv1_agents[0]['id'], $type_id);

            array_push($lv1_data, $lv1_agents, $lv1_rebate);
            array_push($agents_array, $lv1_data);

            // children of first level 1 agent
            $children_ids = User::find($lv1_agents[0]['id'])->getChildrenIds();

            $agents = User::whereIn('id', $children_ids)->where('role', 'agent')
                ->get()->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
                        'name' => $user->name,
                        'hierarchy_list' => $user->hierarchyList,
                        'upline_id' => $user->upline_id,
                        'level' => $this->calculateLevel($user->hierarchyList),
                    ];
                })
                ->groupBy('level')->toArray();

            // push lvl 2 and above agent & rebate into array 
            for ($i = 2; $i <= sizeof($agents) + 1; $i++) {
                $temp = [];
                $rebate = $this->getRebateAllocate($agents[$i][0]['id'], $type_id);

                array_push($temp, $agents[$i], $rebate);
                array_push($agents_array, $temp);
            }
        }
        else {
            // selected agent details
            $agent = User::where('id', $selected_agent_id)->first();

            // find the upper hierarchy of selected agent
            $split_hierarchy = explode('-2-', $agent->hierarchyList);
            $upline_ids = explode('-', $split_hierarchy[1]);

            array_push($upline_ids, $selected_agent_id);
            // dd($upline_ids);

            $uplines = User::whereIn('id', $upline_ids)->get()
                ->map(function($upline) use ($type_id) {
                    $rebate = $this->getRebateAllocate($upline->id, $type_id);

                    $same_level_agents = User::where(['hierarchyList' => $upline->hierarchyList, 'role' => 'agent'])->get()
                        ->map(function($same_level_agent) {
                            return [
                                'id' => $same_level_agent->id,
                                'profile_photo' => $same_level_agent->getFirstMediaUrl('profile_photo'),
                                'name' => $same_level_agent->name,
                                'hierarchy_list' => $same_level_agent->hierarchyList,
                                'upline_id' => $same_level_agent->upline_id,
                                'level' => $this->calculateLevel($same_level_agent->hierarchyList),
                            ];
                        })
                        ->sortBy(fn($agent) => $agent['id'] != $upline->id)
                        ->toArray();

                    // reindex
                    $same_level_agents = array_values($same_level_agents);

                    $data = [];
                    array_push($data, $same_level_agents, $rebate);
                    return $data;
                })->toArray(); 

            // children of selected agent
            $children_ids = User::where('id', $selected_agent_id)->first()->getChildrenIds();
            if ($children_ids) {
                $agents = User::whereIn('id', $children_ids)->where('role', 'agent')
                ->get()->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
                        'name' => $user->name,
                        'hierarchy_list' => $user->hierarchyList,
                        'upline_id' => $user->upline_id,
                        'level' => $this->calculateLevel($user->hierarchyList),
                    ];
                })
                ->groupBy('level')->toArray();

                // reindex
                $agents = array_values($agents);

                // push donward hierarchy agent & rebate into array 
                for ($i = 0; $i < sizeof($agents); $i++) {
                    $temp = [];
                    $rebate = $this->getRebateAllocate($agents[$i][0]['id'], $type_id);

                    array_push($temp, $agents[$i], $rebate);
                    array_push($uplines, $temp);
                }
            }

            $agents_array = $uplines;
        }

        return response()->json($agents_array);
    }
}
