<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Wallet;
use App\Models\Country;
use App\Models\AccountType;
use App\Models\TradingUser;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\GroupHasUser;
use Illuminate\Http\Request;
use App\Models\PaymentAccount;
use App\Models\TradingAccount;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Models\RebateAllocation;
use App\Services\CTraderService;
use App\Models\AssetSubscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\RunningNumberService;
use App\Http\Requests\AddMemberRequest;
use App\Services\DropdownOptionService;
use App\Services\ChangeTraderBalanceType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function listing(Request $request)
    {
        $userRole = $request->query('user_role'); // Get the user_type from the query string

        return Inertia::render('Member/Listing/MemberListing',[
            'user_role' => $userRole, // Pass the user_role to the frontend
            'users' => User::all(), // Pass the user data
        ]);
    }

    public function getMemberListingData()
    {
        $query = User::with(['groupHasUser'])
            ->whereNotIn('role', ['super-admin','admin'])
            ->latest()
            ->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'upline_id' => $user->upline_id,
                    'role' => $user->role,
                    'id_number' => $user->id_number,
                    'group_id' => $user->groupHasUser->group_id ?? null,
                    'group_name' => $user->groupHasUser->group->name ?? null,
                    'group_color' => $user->groupHasUser->group->color ?? null,
                    'status' => $user->status,
                    'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
                ];
            });

        return response()->json([
            'users' => $query
        ]);
    }

    public function getFilterData()
    {
        return response()->json([
            'countries' => (new DropdownOptionService())->getCountries(),
            'uplines' => (new DropdownOptionService())->getUplines(),
            'groups' => (new DropdownOptionService())->getGroups(),
        ]);
    }

    public function addNewMember(AddMemberRequest $request)
    {
        $upline_id = $request->upline['value'];
        $upline = User::find($upline_id);

        if(empty($upline->hierarchyList)) {
            $hierarchyList = "-" . $upline_id . "-";
        } else {
            $hierarchyList = $upline->hierarchyList . $upline_id . "-";
        }

        $dial_code = $request->dial_code;
        $country = Country::find($dial_code['id']);
        $default_agent_id = User::where('id_number', 'AID00000')->first()->id;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'country' => $request->country,
            'dial_code' => $dial_code['phone_code'],
            'phone' => $request->phone,
            'phone_number' => $request->phone_number,
            'upline_id' => $upline_id,
            'country_id' => $country->id,
            'nationality' => $country->nationality,
            'hierarchyList' => $hierarchyList,
            'password' => Hash::make($request->password),
            'role' => $upline_id == $default_agent_id ? 'agent' : 'member',
            'kyc_approval' => 'verified',
        ]);

        $user->setReferralId();

        $id_no = ($user->role == 'agent' ? 'AID' : 'MID') . Str::padLeft($user->id - 2, 5, "0");
        $user->id_number = $id_no;
        $user->save();

        if ($upline->groupHasUser) {
            $user->assignedGroup($upline->groupHasUser->group_id);
        }

        if ($user->role == 'agent') {
            Wallet::create([
                'user_id' => $user->id,
                'type' => 'rebate_wallet',
                'address' => str_replace('AID', 'RB', $user->id_number),
                'balance' => 0
            ]);

            $uplineRebates = RebateAllocation::where('user_id', $user->upline_id)->get();

            foreach ($uplineRebates as $uplineRebate) {
                RebateAllocation::create([
                    'user_id' => $user->id,
                    'account_type_id' => $uplineRebate->account_type_id,
                    'symbol_group_id' => $uplineRebate->symbol_group_id,
                    'amount' => 0,
                    'edited_by' => Auth::id(),
                ]);
            }
        }

        return back()->with('toast', [
            'title' => trans("public.toast_create_member_success"),
            'type' => 'success',
        ]);
    }

    public function updateMemberStatus(Request $request)
    {
        $user = User::find($request->id);

        $user->status = $user->status == 'active' ? 'inactive' : 'active';
        $user->save();

        return back()->with('toast', [
            'title' => $user->status == 'active' ? trans("public.toast_member_has_activated") : trans("public.toast_member_has_deactivated"),
            'type' => 'success',
        ]);
    }

    public function getAvailableUplines(Request $request)
    {
        $role = $request->input('role', ['agent', 'member']);

        $memberId = $request->input('id');

        // Fetch the member and get their children (downline) IDs
        $member = User::findOrFail($memberId);
        $excludedIds = $member->getChildrenIds();
        $excludedIds[] = $memberId;

        // Fetch uplines who are not in the excluded list
        $uplines = User::whereIn('role', (array) $role)
            ->whereNotIn('id', $excludedIds)
            ->get()
            ->map(function ($user) {
                return [
                    'value' => $user->id,
                    'name' => $user->name,
                    'profile_photo' => $user->getFirstMediaUrl('profile_photo')
                ];
            });

        // Return the uplines as JSON
        return response()->json([
            'uplines' => $uplines
        ]);
    }

    public function transferUpline(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'upline_id' => 'required|exists:users,id',
            'role'      => 'required|in:agent,member',
        ]);

        // Find the user to be transferred
        $user = User::findOrFail($request->input('user_id'));

        // Check if the new upline is valid and not the same as the current one
        if ($user->upline_id === $request->input('upline_id')) {
            return back()->with('toast', [
                'title' => trans('public.transfer_same_upline_warning'),
                'type'  => 'warning',
            ]);
        }

        // Find the new upline
        $newUpline = User::findOrFail($request->input('upline_id'));

        // Step 1: Update the user's hierarchyList to reflect the new upline's hierarchy and ID
        $user->hierarchyList = $newUpline->hierarchyList . $newUpline->id . '-';
        $user->upline_id = $newUpline->id;

        // Update the user's group relationship
        if ($newUpline->groupHasUser) {
            $user->assignedGroup($newUpline->groupHasUser->group_id);
        }

        // Save the updated hierarchyList and upline_id for the user
        $user->save();

        // Step 2: If the role is 'agent' for the transferred user, set their RebateAllocation amounts to 0
        if ($request->input('role') === 'agent') {
            RebateAllocation::where('user_id', $user->id)->update(['amount' => 0]);
        }

        // Step 3: Update related users' hierarchyList and their RebateAllocation amounts if they are agents
        $relatedUsers = User::where('hierarchyList', 'like', '%-' . $user->id . '-%')->get();

        foreach ($relatedUsers as $relatedUser) {
            $userIdSegment = '-' . $user->id . '-';

            // Find the position of `-user_id-` in the related user's hierarchyList
            $pos = strpos($relatedUser->hierarchyList, $userIdSegment);

            if ($pos !== false) {
                // Extract the part after the user's ID segment (tail part)
                $tailHierarchy = substr($relatedUser->hierarchyList, $pos + strlen($userIdSegment));

                // Prepend the user's new hierarchyList + user ID to the tail part
                $relatedUser->hierarchyList = $user->hierarchyList . $user->id . '-' . $tailHierarchy;
            }

            // Save the updated hierarchyList for the related user
            $relatedUser->save();

            // Step 4: If the related user is an agent, set their RebateAllocation amounts to 0
            if ($relatedUser->role === 'agent') {
                RebateAllocation::where('user_id', $relatedUser->id)->update(['amount' => 0]);
            }
        }

        // Step 5: Update the related user group has user as transfer upline will change group as well
        if ($group_id = $newUpline->groupHasUser->group_id ?? null) {
            $relatedUserIds = $relatedUsers->pluck('id')->toArray();

            // Perform a bulk update on the 'group_has_user' table for all related users
            GroupHasUser::whereIn('user_id', $relatedUserIds)
                ->where('group_id', '!=', $group_id) // Only update if the current group is different
                ->update(['group_id' => $group_id]);
        }

        // Return a success response
        return back()->with('toast', [
            'title' => trans('public.toast_transfer_upline_success'),
            'type'  => 'success',
        ]);
    }

    public function getAvailableUplineData(Request $request)
    {
        $user = User::with('upline')->find($request->user_id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $availableUpline = $user->upline;

        while ($availableUpline && $availableUpline->role != 'agent') {
            $availableUpline = $availableUpline->upline;
        }

        $availableUpline->profile_photo = $availableUpline->getFirstMediaUrl('profile_photo');

        $uplineRebate = RebateAllocation::with('symbol_group:id,display')
            ->where('user_id', $availableUpline->id);

        $availableAccountTypeId = $uplineRebate->get()->pluck('account_type_id')->toArray();

        $accountTypeSel = AccountType::whereIn('id', $availableAccountTypeId)
            ->select('id', 'name')
            ->get()
            ->map(function ($accountType) {
                return [
                    'id' => $accountType->id,
                    'name' => $accountType->name,
                ];
            });

        return response()->json([
            'availableUpline' => $availableUpline,
            'rebateDetails' => $uplineRebate->get(),
            'accountTypeSel' => $accountTypeSel,
        ]);
    }

    public function upgradeAgent(Request $request)
    {
        $user_id = $request->user_id;
        $amounts = $request->amounts;

        // Find the upline user and their rebate allocations
        $user = User::find($user_id);
        $upline_user = $user->upline;
        $uplineRebates = RebateAllocation::where('user_id', $upline_user->id)->get();

        // Get the account_type_id and symbol_group_id combinations for the upline
        $uplineCombinations = $uplineRebates->map(function($rebate) {
            return [
                'account_type_id' => $rebate->account_type_id,
                'symbol_group_id' => $rebate->symbol_group_id
            ];
        })->toArray();

        // Get the account_type_id and symbol_group_id combinations from the request
        $requestCombinations = array_map(function($amount) {
            return [
                'account_type_id' => $amount['account_type_id'],
                'symbol_group_id' => $amount['symbol_group_id']
            ];
        }, $amounts);

        $errors = [];

        // Validate amounts
        foreach ($amounts as $index => $amount) {
            $uplineRebate = RebateAllocation::find($amount['rebate_detail_id']);

            if ($uplineRebate && $amount['amount'] > $uplineRebate->amount) {
                $errors["amounts.$index"] = 'Amount should not be higher than $' . $uplineRebate->amount;
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        // Create rebate allocations for amounts in the request
        foreach ($amounts as $amount) {
            RebateAllocation::create([
                'user_id' => $user_id,
                'account_type_id' => $amount['account_type_id'],
                'amount' => $amount['amount'],
                'symbol_group_id' => $amount['symbol_group_id'],
                'edited_by' => Auth::id()
            ]);
        }

        // Create entries for missing combinations with amount 0
        foreach ($uplineCombinations as $combination) {
            if (!in_array($combination, $requestCombinations)) {
                RebateAllocation::create([
                    'user_id' => $user_id,
                    'account_type_id' => $combination['account_type_id'],
                    'amount' => 0,
                    'symbol_group_id' => $combination['symbol_group_id'],
                    'edited_by' => Auth::id()
                ]);
            }
        }

        $user->id_number = $request->id_number;
        $user->role = 'agent';
        $user->save();

        Wallet::create([
            'user_id' => $user->id,
            'type' => 'rebate_wallet',
            'address' => str_replace('AID', 'RB', $user->id_number),
            'balance' => 0
        ]);

        return back()->with('toast', [
            'title' => trans('public.upgrade_to_agent_success_alert'),
            'type' => 'success',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', Password::min(8)->letters()->numbers()->symbols()->mixedCase(), 'confirmed'],
            'password_confirmation' => ['required','same:password'],
        ])->setAttributeNames([
            'password' => trans('public.password'),
            'password_confirmation' => trans('public.confirm_password')
        ]);
        $validator->validate();

        $user = User::find($request->id);
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('toast', [
            'title' => trans('public.toast_reset_password_success'),
            'type' => 'success'
        ]);

    }

    public function detail($id_number)
    {
        $user = User::where('id_number', $id_number)->select('id', 'name')->first();

        return Inertia::render('Member/Listing/Partials/MemberListingDetail', [
            'user' => $user
        ]);
    }

    public function getUserData(Request $request)
    {
        $user = User::with(['groupHasUser', 'upline:id,name'])->find($request->id);

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'dial_code' => $user->dial_code,
            'phone' => $user->phone,
            'upline_id' => $user->upline_id,
            'role' => $user->role,
            'id_number' => $user->id_number,
            'status' => $user->status,
            'profile_photo' => $user->getFirstMediaUrl('profile_photo'),
            'group_id' => $user->groupHasUser->group_id ?? null,
            'group_name' => $user->groupHasUser->group->name ?? null,
            'group_color' => $user->groupHasUser->group->color ?? null,
            'upline_name' => $user->upline->name ?? null,
            'upline_profile_photo' => $user->upline ? $user->upline->getFirstMediaUrl('profile_photo') : null,
            'total_direct_member' => $user->directChildren->where('role', 'member')->count(),
            'total_direct_agent' => $user->directChildren->where('role', 'agent')->count(),
            'kyc_verification' => $user->getFirstMedia('kyc_verification'),
            'kyc_approved_at' => $user->kyc_approved_at,
        ];

        $paymentAccounts = $user->paymentAccounts()
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'payment_account_name' => $account->payment_account_name,
                    'account_no' => $account->account_no,
                ];
            });

        return response()->json([
            'userDetail' => $userData,
            'paymentAccounts' => $paymentAccounts
        ]);
    }

    public function updateContactInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($request->user_id)],
            'name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'dial_code' => ['required'],
            'phone' => ['required', 'max:255'],
            'phone_number' => ['required', 'max:255', Rule::unique(User::class)->ignore($request->user_id)],
        ])->setAttributeNames([
            'email' => trans('public.email'),
            'name' => trans('public.name'),
            'dial_code' => trans('public.phone_code'),
            'phone' => trans('public.phone'),
            'phone_number' => trans('public.phone_number'),
        ]);
        $validator->validate();

        $user = User::findOrFail($request->user_id);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'dial_code' => $request->dial_code['phone_code'],
            'phone' => $request->phone,
            'phone_number' => $request->phone_number,
        ]);

        return redirect()->back()->with('toast', [
            'title' => trans('public.update_contact_info_alert'),
            'type' => 'success'
        ]);
    }

    public function updateCryptoWalletInfo(Request $request)
    {
        $wallet_names = $request->wallet_name;
        $token_addresses = $request->token_address;

        $errors = [];

        // Validate wallets and addresses
        foreach ($wallet_names as $index => $wallet_name) {
            $token_address = $token_addresses[$index] ?? '';

            if (empty($wallet_name) && !empty($token_address)) {
                $errors["wallet_name.$index"] = trans('validation.required', ['attribute' => trans('public.wallet_name') . ' #' . ($index + 1)]);
            }

            if (!empty($wallet_name) && empty($token_address)) {
                $errors["token_address.$index"] = trans('validation.required', ['attribute' => trans('public.token_address') . ' #' . ($index + 1)]);
            }
        }

        foreach ($token_addresses as $index => $token_address) {
            $wallet_name = $wallet_names[$index] ?? '';

            if (empty($token_address) && !empty($wallet_name)) {
                $errors["token_address.$index"] = trans('validation.required', ['attribute' => trans('public.token_address') . ' #' . ($index + 1)]);
            }

            if (!empty($token_address) && empty($wallet_name)) {
                $errors["wallet_name.$index"] = trans('validation.required', ['attribute' => trans('public.wallet_name') . ' #' . ($index + 1)]);
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        if ($wallet_names && $token_addresses) {
            foreach ($wallet_names as $index => $wallet_name) {
                // Skip iteration if id or token_address is null
                if (is_null($token_addresses[$index])) {
                    continue;
                }

                $conditions = [
                    'user_id' => $request->user_id,
                ];

                // Check if 'id' is set and valid
                if (!empty($request->id[$index])) {
                    $conditions['id'] = $request->id[$index];
                } else {
                    $conditions['id'] = 0;
                }

                PaymentAccount::updateOrCreate(
                    $conditions,
                    [
                        'status' => 'active',
                        'payment_account_name' => $wallet_name,
                        'payment_platform' => 'crypto',
                        'payment_platform_name' => 'USDT (TRC20)',
                        'account_no' => $token_addresses[$index],
                        'currency' => 'USDT'
                    ]
                );
            }
        }

        return redirect()->back()->with('toast', [
            'title' => trans('public.update_contact_info_alert'),
            'type' => 'success'
        ]);
    }

    public function updateKYCStatus(Request $request)
    {
        $user = User::find($request->id);

        $user->kyc_approved_at = null;
        $user->save();

        return redirect()->back()->with('toast', [
            'title' => trans('public.toast_kyc_upload_request'),
            'type' => 'success'
        ]);
    }

    public function getFinancialInfoData(Request $request)
    {
        $query = Transaction::query()
            ->where('user_id', $request->id)
            ->where('category', 'trading_account')
            ->where('status', 'successful');

        $total_deposit = (clone $query)->where('transaction_type', 'deposit')->sum('transaction_amount');
        $total_withdrawal = (clone $query)->where('transaction_type', 'withdrawal')->sum('amount');
        $transaction_history = $query->whereIn('transaction_type', ['deposit', 'withdrawal', 'balance_in', 'balance_out'])
            ->latest()
            ->get()
            ->map(function ($transaction) {
                return [
                    'category' => $transaction->category,
                    'transaction_type' => $transaction->transaction_type,
                    'from_meta_login' => $transaction->from_meta_login,
                    'to_meta_login' => $transaction->to_meta_login,
                    'amount' => $transaction->amount,
                    'transaction_charges' => $transaction->transaction_charges,
                    'transaction_amount' => $transaction->transaction_amount,
                    'status' => $transaction->status,
                    'comment' => $transaction->comment,
                    'remarks' => $transaction->remarks,
                    'created_at' => $transaction->created_at,
                    'approved_at' => $transaction->approved_at,
                ];
            });

        $rebate_wallet = Wallet::where('user_id', $request->id)
            ->where('type', 'rebate_wallet')
            ->first();

        return response()->json([
            'totalDeposit' => $total_deposit,
            'totalWithdrawal' => $total_withdrawal,
            'transactionHistory' => $transaction_history,
            'rebateWallet' => $rebate_wallet,
        ]);
    }

    public function walletAdjustment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => ['required'],
            'amount' => ['required', 'numeric', 'gt:1'],
            'remarks' => ['nullable'],
        ])->setAttributeNames([
            'action' => trans('public.action'),
            'amount' => trans('public.amount'),
            'remarks' => trans('public.remarks'),
        ]);
        $validator->validate();

        $action = $request->action;
        $amount = $request->amount;
        $wallet = Wallet::find($request->id);

        if ($action == 'rebate_out' && $wallet->balance < $amount) {
            throw ValidationException::withMessages(['amount' => trans('public.insufficient_balance')]);
        }

        Transaction::create([
            'user_id' => $wallet->user_id,
            'category' => 'wallet',
            'transaction_type' => $action,
            'from_wallet_id' => $action == 'rebate_out' ? $wallet->id : null,
            'to_wallet_id' => $action == 'rebate_in' ? $wallet->id : null,
            'transaction_number' => RunningNumberService::getID('transaction'),
            'amount' => $amount,
            'transaction_charges' => 0,
            'transaction_amount' => $amount,
            'old_wallet_amount' => $wallet->balance,
            'new_wallet_amount' => $action == 'rebate_out' ? $wallet->balance - $amount : $wallet->balance + $amount,
            'status' => 'successful',
            'remarks' => $request->remarks,
            'approved_at' => now(),
            'handle_by' => Auth::id(),
        ]);

        $wallet->balance = $action === 'rebate_out' ? $wallet->balance - $amount : $wallet->balance + $amount;
        $wallet->save();

        return redirect()->back()->with('toast', [
            'title' => trans('public.rebate_adjustment_success'),
            'type' => 'success'
        ]);
    }

    public function getTradingAccounts(Request $request)
    {
        // Fetch trading accounts based on user ID
        $tradingAccounts = TradingAccount::query()
            ->where('user_id', $request->id)
            ->get() // Fetch the results from the database
            ->map(function($trading_account) {
                $following_master = AssetSubscription::with('asset_master:id,asset_name')
                    ->where('meta_login', $trading_account->meta_login)
                    ->whereIn('status', ['ongoing', 'pending'])
                    ->first();

                $remaining_days = null;

                if ($following_master && $following_master->matured_at) {
                    $matured_at = Carbon::parse($following_master->matured_at);
                    $remaining_days = Carbon::now()->diffInDays($matured_at);
                }
                return [
                    'id' => $trading_account->id,
                    'meta_login' => $trading_account->meta_login,
                    'account_type' => $trading_account->accountType->slug,
                    'balance' => $trading_account->balance,
                    'credit' => $trading_account->credit,
                    'equity' => $trading_account->equity,
                    'leverage' => $trading_account->margin_leverage,
                    'account_type_color' => $trading_account->accountType->color,
                    'updated_at' => $trading_account->updated_at,
                    'asset_master_id' => $following_master->asset_master->id ?? null,
                    'asset_master_name' => $following_master->asset_master->asset_name ?? null,
                    'remaining_days' => intval($remaining_days),
                ];
            });

        // Return the response as JSON
        return response()->json([
            'tradingAccounts' => $tradingAccounts,
        ]);
    }

    public function getAdjustmentHistoryData(Request $request)
    {
        $adjustment_history = Transaction::where('user_id', $request->id)
            ->whereIn('transaction_type', ['rebate_in', 'rebate_out', 'balance_in','balance_out','credit_in','credit_out',])
            ->where('status', 'successful')
            ->latest()
            ->get();

        return response()->json($adjustment_history);
    }

    public function uploadKyc(Request $request)
    {
        dd($request->all());
    }

    public function deleteMember(Request $request)
    {
        $user = User::find($request->id);

        $relatedUsers = User::where('hierarchyList', 'like', '%-' . $user->id . '-%')->get();

        foreach ($relatedUsers as $relatedUser) {
            $updatedHierarchyList = str_replace('-' . $user->id . '-', '-', $relatedUser->hierarchyList);

            $relatedUser->hierarchyList = $updatedHierarchyList;

            // Split the updated hierarchyList to find the new upline
            $hierarchyArray = array_filter(explode('-', $updatedHierarchyList));

            // Since the last element is the `upline_id`, find the new upline
            if (!empty($hierarchyArray)) {
                // Get the last element in the array, which is the new upline_id
                $newUplineId = end($hierarchyArray);
                $relatedUser->upline_id = $newUplineId;
            } else {
                $relatedUser->upline_id = null;
            }
            $relatedUser->save();
        }

        $user->transactions()->delete();
        $user->tradingAccounts()->delete();
        $user->tradingUsers()->delete();
        $user->paymentAccounts()->delete();
        $user->rebateAllocations()->delete();
        $user->delete();

        return redirect()->back()->with('toast', [
            'title' => trans('public.toast_delete_member_success'),
            'type' => 'success'
        ]);
    }

    public function access_portal(User $user)
    {
        $dataToHash = $user->name . $user->email . $user->id_number;
        $hashedToken = md5($dataToHash);

        $currentHost = $_SERVER['HTTP_HOST'];

        // Retrieve the app URL and parse its host
        $appUrl = parse_url(config('app.url'), PHP_URL_HOST);
        $memberProductionUrl = config('app.member_production_url');

        if ($currentHost === 'mosanes-admin.currenttech.pro') {
            $url = "https://mosanes-user.currenttech.pro/admin_login/$hashedToken";
        } elseif ($currentHost === $appUrl) {
            $url = "$memberProductionUrl/admin_login/$hashedToken";
        } else {
            return back();
        }

        $params = [
            'admin_id' => Auth::id(),
            'admin_name' => Auth::user()->name,
        ];

        $redirectUrl = $url . "?" . http_build_query($params);
        return Inertia::location($redirectUrl);
    }
}
