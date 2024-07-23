<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
use App\Models\Group;
use App\Models\GroupHasUser;
use App\Models\User;
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
                ];
            });

        return response()->json([
            'groups' => $groups
        ]);
    }

    public function loadAgents()
    {
        $has_group = GroupHasUser::pluck('user_id');

        $users = User::where('role', 'agent')
            ->whereNotIn('id', $has_group)
            ->select('id', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'value' => $user->id,
                    'name' => $user->name,
                    'profile_photo' => $user->getFirstMediaUrl('profile_photo')
                ];
            });

        return response()->json($users);
    }

    public function createGroup(GroupRequest $request)
    {
        // dd($request->all());
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
            'title' => "You've successfully created a new group!",
            'type' => 'success',
        ]);
    }

    public function editGroup(Request $request)
    {
        // dd($request->all());

        return back()->with('toast', [
            'title' => "You've successfully updated the group details!",
            'type' => 'success',
        ]);
    }

    public function deleteGroup($id)
    {
        Group::destroy($id);

        GroupHasUser::where('group_id', $id)->delete();

        return back()->with('toast', [
            'title' => "Group has been deleted!",
            'type' => 'success',
        ]);
    }
}
