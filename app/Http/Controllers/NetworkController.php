<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NetworkController extends Controller
{
    public function network()
    {
        $upline = User::where('id_number', 'AID00000')
            ->select('id', 'name', 'id_number')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'id_number' => $user->id_number,
                    'profile_photo' => $user->getFirstMediaUrl('profile_photo')
                ];
            });

        return Inertia::render('Member/Network/MemberNetwork', [
            'upline' => $upline
        ]);
    }

    public function getDownlineData(Request $request)
    {
        $upline_id = $request->upline_id;
        $parent_id = $request->parent_id ?: 2;

        $parent = User::with(['directChildren:id,name,id_number,upline_id,role,hierarchyList'])
            ->select('id', 'name', 'id_number', 'upline_id', 'role', 'hierarchyList')
            ->find($parent_id);

        $upline = $upline_id ? User::select('id', 'name', 'id_number', 'upline_id', 'role', 'hierarchyList')->find($upline_id) : null;

        $parent_data = $this->formatUserData($parent);
        $upline_data = $upline ? $this->formatUserData($upline) : null;

        $direct_children = $parent->directChildren->map(function ($child) {
            return $this->formatUserData($child);
        });

        return response()->json([
            'upline' => $upline_data,
            'parent' => $parent_data,
            'direct_children' => $direct_children,
        ]);
    }

    private function formatUserData($user)
    {
        return array_merge(
            $user->only(['id', 'name', 'id_number', 'upline_id', 'role']),
            [
                'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
                'level' => $this->calculateLevel($user->hierarchyList),
                'total_agent_count' => $this->getChildrenCount('agent', $user->id),
                'total_member_count' => $this->getChildrenCount('member', $user->id),
            ]
        );
    }

    private function calculateLevel($hierarchyList)
    {
        if (is_null($hierarchyList) || $hierarchyList === '') {
            return 0;
        }
        return substr_count($hierarchyList, '-') - 1;
    }

    private function getChildrenCount($role, $user_id): int
    {
        return User::where('role', $role)
            ->where('hierarchyList', 'like', '%-' . $user_id . '-%')
            ->count();
    }
}
