<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function show()
    {
        return Inertia::render('Group/Group');
    }

    public function createGroup(Request $request)
    {
        // dd($request->all());

        return back()->with('toast', [
            'title' => "You've successfully created a new group!",
            'type' => 'success',
        ]);
    }
}
