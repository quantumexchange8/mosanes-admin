<?php

namespace App\Http\Controllers;

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
            'title' => 'test',
            'type' => 'success'
        ]);
    }


    public function detail()
    {
        return Inertia::render('Member/Listing/Partials/MemberListingDetail');
    }

    public function loadCountries(Request $request)
    {
        $countries = collect([
            ['country' => 'United States', 'dial_code' => '+1'],
            ['country' => 'Italy', 'dial_code' => '+39'],
            ['country' => 'United Kingdom', 'dial_code' => '+44' ],
            ['country' => 'Turkey', 'dial_code' => '+90' ],
            ['country' => 'France', 'dial_code' => '+33' ]
        ]);

        return response()->json($countries);
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
