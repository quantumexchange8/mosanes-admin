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

        // get getDirectAgents of first upline
        $loop_flag = true;
        $current_agent_id = $lv1_agents[0]['id'];
        while ($loop_flag) {
            $next_level = $this->getDirectAgents($current_agent_id, $type_id);
            if ( !empty($next_level) ) {
                $current_agent_id = $next_level[0][0]['id'];
                array_push($agents_array, $next_level);
            } else {
                $loop_flag = false;
            }
        }

        return response()->json($agents_array);
    }

    private function getDirectAgents($agent_id, $type_id)
    {
        // children of id passed in
        $children = User::find($agent_id)->directChildren()->where('role', 'agent')->select('id', 'hierarchyList')->get();

        // find children same level
        if ( $children->isNotEmpty() ) {
            $agents = User::where(['hierarchyList' => $children[0]->hierarchyList, 'role' => 'agent'])->get()
                ->map(function ($agent) {
                    return [
                        'id' => $agent->id,
                        'profile_photo' => $agent->getFirstMediaUrl('profile_photo'),
                        'name' => $agent->name,
                        'hierarchy_list' => $agent->hierarchyList,
                        'upline_id' => $agent->upline_id,
                        'level' => $this->calculateLevel($agent->hierarchyList),
                    ];
                })
                ->sortBy(fn($agent) => $agent['id'] != $children[0]->id)
                ->toArray();

            // reindex
            $agents = array_values($agents);

            // push current level hierarchy agent & rebate into array 
            $temp = [];
            $rebate = $this->getRebateAllocate($agents[0]['id'], $type_id);

            array_push($temp, $agents, $rebate);

            return $temp;
        }

        return '';
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
        $selected_agent_id = $request->id;
        $type_id = $request->type_id;
        $agents_array = [];

        $selected_agent = User::where('id', $selected_agent_id)->first();

        // determine is the selected agent other than level 1
        if ($selected_agent->upline_id !== 2) {
            $split_hierarchy = explode('-2-', $selected_agent->hierarchyList);
            $upline_ids = explode('-', $split_hierarchy[1]);
            
            array_pop($upline_ids);

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

            $agents_array = $uplines;
        }

        // selected agent & same level agents
        $agents = User::where(['hierarchyList' => $selected_agent->hierarchyList, 'role' => 'agent'])->get()
            ->map(function($agent) {
                return [
                    'id' => $agent->id,
                    'profile_photo' => $agent->getFirstMediaUrl('profile_photo'),
                    'name' => $agent->name,
                    'hierarchy_list' => $agent->hierarchyList,
                    'upline_id' => $agent->upline_id,
                    'level' => $this->calculateLevel($agent->hierarchyList),
                ];
            })
            ->sortBy(fn($agent) => $agent['id'] != $selected_agent->id)
            ->toArray();

        // reindex
        $agents = array_values($agents);

        // selected agent rebate
        $rebate = $this->getRebateAllocate($selected_agent_id, $type_id);

        //push selected agent level into array
        $temp = [];
        array_push($temp, $agents, $rebate);
        array_push($agents_array, $temp);

        //pass to getDirectAgents
        $loop_flag = true;
        $current_agent_id = $selected_agent_id;
        while ($loop_flag) {
            $next_level = $this->getDirectAgents($current_agent_id, $type_id);
            if ( !empty($next_level) ) {
                $current_agent_id = $next_level[0][0]['id'];
                array_push($agents_array, $next_level);
            } else {
                $loop_flag = false;
            }
        }

        return response()->json($agents_array);
    }
}
