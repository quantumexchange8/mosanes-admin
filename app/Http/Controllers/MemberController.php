<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class MemberController extends Controller
{
    public function listing()
    {
        return Inertia::render('Member/Listing/MemberListing');
    }
}
