<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function listing()
    {
        return Inertia::render('Member/Listing/MemberListing');
    }

    public function addNewMember(Request $request)
    {
        // dd($request->all());
        return redirect()->back()->with('toast', [
            'title' => 'You’ve successfully created a new member!',
            'type' => 'success'
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
