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
}
