<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function approve(Request $request, $id)
    {
        // Test mode: no real model yet, just confirm the signed link worked
        return "Approved record #{$id} — signed link verified successfully.";
    }
}
