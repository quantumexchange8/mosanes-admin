<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountTypeRequest;
use App\Models\AccountType;
use App\Services\DropdownOptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountTypeController extends Controller
{
    public function show()
    {
        return Inertia::render('AccountType/AccountType');
    }

    public function getAccountTypes()
    {
        $accountTypes = AccountType::all()
        ->map(function($accountType) {
            $accountType['total_account'] = 21;

            return $accountType;
        });
        
        return response()->json(['accountTypes' => $accountTypes]);
    }

    public function syncAccountTypes()
    {
        //function

        return back()->with('toast', [
            'title' => 'Account type has been synchronised.',
            'type'=> 'success',
        ]);
    }

    public function getLeverages()
    {
        return response()->json([
            'leverages' => (new DropdownOptionService())->getLeverages(),
        ]);
    }

    public function updateAccountType(UpdateAccountTypeRequest $request, $id)
    {
        $account_type = AccountType::find($id);
        $account_type->category = $request->category;
        $account_type->descriptions = $request->description;
        $account_type->leverage = $request->leverage;
        $account_type->trade_open_duration = $request->trade_delay_duration;
        $account_type->maximum_account_number = $request->max_account;
        // $account_type->status = 'active';
        $account_type->save();

        return back()->with('toast', [
            'title' => "You've successfully updated the account type!",
            'type' => 'success',
        ]);
    }
}
