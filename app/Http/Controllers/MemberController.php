<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddMemberRequest;
use App\Models\Country;
use App\Models\User;
use App\Models\Wallet;
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
        $users = User::latest()
            ->get();

        return response()->json([
            'users' => $users
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
            'role' => 'user',
            'kyc_approval' => 'verified',
        ]);

        $user->setReferralId();

        $id_no = 'MID' . Str::padLeft($user->id, 5, "0");
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

    public function loadCountries()
    {
        $countries = Country::get()->map(function ($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'phone_code' => $country->phone_code,
            ];
        });

        return response()->json($countries);
    }

    public function loadUplines()
    {
        $users = User::where('role', 'super-admin')
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


}
