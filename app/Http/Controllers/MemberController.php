<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddMemberRequest;
use App\Models\Country;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DropdownOptionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
            ->latest();

        return response()->json([
            'users' => $query->get()->map(function ($user) {
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
            }),
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

    public function addNewMember(Request $request)
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
            'title' => "You’ve successfully created a new member!",
            'type' => 'success',
        ]);
    }


    public function detail()
    {
        return Inertia::render('Member/Listing/Partials/MemberListingDetail');
    }

    public function updateContactInfo(Request $request)
    {
        // dd($request->all());
        return redirect()->back()->with('toast', [
            'title' => 'test',
            'type' => 'success'
        ]);
    }

    public function updateCryptoWalletInfo(Request $request)
    {
        dd($request->all());
    }

    public function updateKYCStatus(Request $request)
    {
        dd($request->all());
    }

    public function cashWalletAdjustment(Request $request)
    {
        dd($request->all());
    }

    public function rebateWalletAdjustment(Request $request)
    {
        dd($request->all());
    }

    public function accountBalanceAdjustment(Request $request)
    {
        dd($request->all());
    }

    public function accountCreditAdjustment(Request $request)
    {
        dd($request->all());
    }

    public function accountDelete(Request $request)
    {
        // dd($request->all());
        return redirect()->back()->with('toast', [
            'title' => 'Trading account has been deleted!',
            'type' => 'success'
        ]);

    }

    public function uploadKyc(Request $request)
    {
        dd($request->all());
    }
}
