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

    /**
     * Switch the active office for the current user
     */
    public function switchOffice(Request $request)
    {
        $officeCode = $request->input('office_code');
        
        // Validate that the office exists
        if ($officeCode) {
            // Store the selected office code in session
            session(['active_office_code' => $officeCode]);
            
            // Optional: also store the office ID if needed elsewhere
            // $office = Office::where('code', $officeCode)->first();
            // if ($office) {
            //     session(['current_office_id' => $office->ID]);
            // }
        }
        
        // Redirect back to the previous page with a success message
        return redirect()->back()->with('success', 'Office switched successfully');
    }
}