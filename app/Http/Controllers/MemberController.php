<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddMemberRequest;
use App\Models\Country;
use App\Models\PaymentAccount;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DropdownOptionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function listing()
    {
        return Inertia::render('Member/Listing/MemberListing');
    }

    public function getMemberListingData()
    {
        $query = User::with(['groupHasUser'])
            ->whereNot('role', 'super-admin')
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

        return back()->with('toast', [
            'title' => trans("public.toast_create_member_success"),
            'type' => 'success',
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
            'kyc_verification' => $user->getMedia('kyc_verification'),
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
            'dial_code' => trans('public.dial_code'),
            'phone' => trans('public.phone'),
            'phone_number' => trans('public.phone_number'),
        ]);
        $validator->validate();

        return redirect()->back()->with('toast', [
            'title' => trans('public.update_contact_info_alert'),
            'type' => 'success'
        ]);
    }

    public function updateCryptoWalletInfo(Request $request)
    {
        $wallet_names = $request->wallet_name;
        $token_addresses = $request->token_address;

//        $errors = [];
//
//        // Validate wallets
//        foreach ($wallet_names as $index => $wallet_name) {
//            if (empty($wallet_name)) {
//                $errors["wallet_name.$index"] = trans('validation.required', ['attribute' => trans('public.wallet_name') . ' #' .$index + 1]);
//            }
//        }
//
//        // Validate addresses
//        foreach ($token_addresses as $index => $token_address) {
//            if (empty($token_address)) {
//                $errors["token_address.{$index}"] = trans('validation.required', ['attribute' => trans('public.token_address') . ' #' .$index + 1]);
//            }
//        }
//
//        if (!empty($errors)) {
//            throw ValidationException::withMessages($errors);
//        }

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

                \Log::debug($conditions);

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
        dd($request->all());
    }

    public function WalletAdjustment(Request $request)
    {
        dd($request->all());
    }

    public function accountAdjustment(Request $request)
    {
        dd($request->all());
    }

    public function accountDelete(Request $request)
    {
        // dd($request->all());
        return redirect()->back()->with('toast', [
            'title' => trans('public.toast_delete_trading_account_success'),
            'type' => 'success'
        ]);

    }

    public function uploadKyc(Request $request)
    {
        dd($request->all());
    }
}
