<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    /**
     * Get office names
     */
    public function getOfficeName(Request $request)
    {
        if ($request->ajax()) {
            $office_name = Office::select('Name')->where('ID', $request->ID)->first();
            return response()->json($office_name);
        }
    }
}
