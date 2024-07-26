<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditGroupRequest;
use App\Http\Requests\GroupRequest;
use App\Models\Group;
use App\Models\GroupHasUser;
use App\Models\User;
use App\Services\DropdownOptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function show()
    {
        return Inertia::render('Group/Group');
    }

    public function getGroups()
    {
        $groups = DB::table('groups')->select('groups.id', 'groups.name', 'groups.fee_charges', 'groups.color', 'groups.group_leader as user_id', 'users.name as leader_name', 'users.email as leader_email', 'groups.deleted_at')
            ->join('users', 'users.id', '=', 'groups.group_leader')
            ->where('groups.deleted_at', '=', null)
            ->get()
            ->map(function($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'fee_charges' => $group->fee_charges,
                    'color' => $group->color,
                    'user_id' => $group->user_id,
                    'leader_name' => $group->leader_name,
                    'leader_email' => $group->leader_email,
                    'profile_photo' => User::find($group->user_id)->getFirstMediaUrl('profile_photo'),
                    'member_count' => GroupHasUser::where('group_id', $group->id)->count(),
                    'deposit' => 0,
                    'withdrawal' => 0,
                    'charges' => 0,
                    'net_balance' => 0,
                    'account_balance' => 0,
                    'account_equity' => 0,
                ];
            });

        $total = [];
        $total['total_net_balance'] = 60340;
        $total['total_deposit'] = 36521;
        $total['total_withdrawal'] = 12054;
        $total['total_charges'] = 24467;

        return response()->json([
            'groups' => $groups,
            'total' => $total,
        ]);
    }

    public function getAgents()
    {
        $users = (new DropdownOptionService())->getAgents();

        return response()->json($users);
    }

    public function createGroup(GroupRequest $request)
    {
        $agent_id = $request->agent['value'];
        $group = Group::create([
            'name' => $request->group_name,
            'fee_charges' => $request->fee_charges,
            'color' => $request->color,
            'group_leader' => $agent_id,
            'edited_by' => Auth::id(),
        ]);

        $group_id = $group->id;
        GroupHasUser::create([
            'group_id' => $group_id,
            'user_id' => $agent_id
        ]);

        $children_ids = User::find($agent_id)->getChildrenIds();
        User::whereIn('id', $children_ids)->chunk(500, function($users) use ($group_id) {
            $users->each->assignedGroup($group_id);
        });

        return back()->with('toast', [
            'title' => trans('public.toast_create_group_success'),
            'type' => 'success',
        ]);
    }

    public function editGroup(EditGroupRequest $request, $id)
    {
        $group = Group::find($id);
        $group->name = $request->group_name;
        $group->fee_charges = $request->fee_charges;
        $group->color = $request->color;
        $group->group_leader = $request->agent['value'];
        $group->edited_by = Auth::id();
        $group->save();

        return back()->with('toast', [
            'title' => trans('public.toast_update_group_success'),
            'type' => 'success',
        ]);
    }

    public function deleteGroup($id)
    {
        Group::destroy($id);

        GroupHasUser::where('group_id', $id)->delete();

        return back()->with('toast', [
            'title' => trans('public.toast_delete_group_success'),
            'type' => 'success',
        ]);
    }
}
