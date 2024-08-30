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
        $groups = Group::get()
            ->map(function ($group) use ($request) {
                $groupUserIds = GroupHasUser::where('group_id', $group->id)
                    ->pluck('user_id')
                    ->toArray();

                $startDate = $request->input('startDate') ?? now()->startOfYear();
                $endDate = $request->input('endDate') ?? today()->endOfDay();

                $total_deposit = Transaction::whereIn('user_id', $groupUserIds)
                    ->whereBetween('approved_at', [$startDate, $endDate])
                    ->where(function ($query) {
                        $query->where('transaction_type', 'deposit')
                            ->orWhere('transaction_type', 'balance_in');
                    })
                    ->where('status', 'successful')
                    ->sum('transaction_amount');

                $total_withdrawal = Transaction::whereIn('user_id', $groupUserIds)
                    ->whereBetween('approved_at', [$startDate, $endDate])
                    ->where(function ($query) {
                        $query->where('transaction_type', 'withdrawal')
                            ->orWhere('transaction_type', 'balance_out')
                            ->orWhere('transaction_type', 'rebate_out');
                    })
                    ->where('status', 'successful')
                    ->sum('amount');

                $transaction_fee_charges = $total_deposit / $group->fee_charges;
                $net_balance = $total_deposit - $transaction_fee_charges - $total_withdrawal;

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'fee_charges' => $group->fee_charges,
                    'color' => $group->color,
                    'leader_name' => $group->leader->name,
                    'leader_email' => $group->leader->email,
                    'profile_photo' => $group->leader->getFirstMediaUrl('profile_photo'),
                    'deposit' => $total_deposit,
                    'withdrawal' => $total_withdrawal,
                    'transaction_fee_charges' => $transaction_fee_charges,
                    'net_balance' => $net_balance,
                    'account_balance' => 0, // calculate balance and equity
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
            'group_leader_id' => $agent_id,
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
        $group->group_leader_id = $request->agent['value'];
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
