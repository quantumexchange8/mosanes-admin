<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Group;
use App\Models\Transaction;
use App\Models\GroupHasUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\GroupRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\EditGroupRequest;
use App\Services\DropdownOptionService;

class GroupController extends Controller
{
    public function show()
    {
        return Inertia::render('Group/Group');
    }

    public function getGroups(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
    
        $query = DB::table('groups')
            ->select(
                'groups.id', 
                'groups.name', 
                'groups.fee_charges', 
                'groups.color', 
                'groups.group_leader as user_id', 
                'users.name as leader_name', 
                'users.email as leader_email', 
                'groups.deleted_at'
            )
            ->join('users', 'users.id', '=', 'groups.group_leader')
            ->whereNull('groups.deleted_at');
    
        // Apply date range filter if startDate and endDate are provided
        if ($startDate && $endDate) {
            // Both startDate and endDate are provided
            $query->whereDate('groups.created_at', '>=', $startDate)
                ->whereDate('groups.created_at', '<=', $endDate);
        } else {
            // Apply default start date if no endDate is provided
            $query->whereDate('groups.created_at', '>=', '2024-01-01');
        }
    
        $groups = $query->get()->map(function($group) {
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
    
        $total = [
            'total_net_balance' => 0,
            'total_deposit' => 0,
            'total_withdrawal' => 0,
            'total_charges' => 0,
        ];
    
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

    public function getGroupTransaction(Request $request)
    {
        $groupId = $request->input('groupId');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
    
        // Get all user IDs associated with the groupId
        $userIds = GroupHasUser::where('group_id', $groupId)
            ->pluck('user_id');
    
        // Start building the query
        $query = Transaction::with('user')
            ->whereIn('user_id', $userIds)
            ->whereIn('transaction_type', ['deposit', 'withdrawal'])
            ->where('status', 'successful');
    
        // Apply date range filter if startDate and endDate are provided
        if ($startDate && $endDate) {
            // Both startDate and endDate are provided
            $query->whereDate('approved_at', '>=', $startDate)
                ->whereDate('approved_at', '<=', $endDate);
        } else {
            // Apply default start date if no endDate is provided
            $query->whereDate('approved_at', '>=', '2024-01-01');
        }
    
        // Execute the query and get the results
        $transactions = $query->get();

        // Map the results to include user details
        $result = $transactions->map(function ($transaction) {
            return [
                'approved_at' => $transaction->approved_at,
                'user_id' => $transaction->user_id,
                'name' => $transaction->user->name,
                'email' => $transaction->user->email,
                'profile_photo' => $transaction->user->getFirstMediaUrl('profile_photo'),
                'transaction_type' => $transaction->transaction_type,
                'amount' => $transaction->amount,
                'transaction_charges' => $transaction->transaction_charges,
                'transaction_amount' => $transaction->transaction_amount,
            ];
        });
    
        // Calculate total values
        $totalAmount = $transactions->sum('amount');
        $totalFee = $transactions->sum('transaction_charges');
        $totalBalance = $transactions->sum('transaction_amount');

        // Return response with totals
        return response()->json([
            'transactions' => $result,
            'totalAmount' => $totalAmount,
            'totalFee' => $totalFee,
            'totalBalance' => $totalBalance,
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