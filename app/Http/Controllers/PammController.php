<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class PammController extends Controller
{
    public function pamm_allocate()
    {
        return Inertia::render('PammAllocate/PammAllocate');
    }
}
