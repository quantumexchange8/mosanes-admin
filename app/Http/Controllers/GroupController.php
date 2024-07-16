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
}
