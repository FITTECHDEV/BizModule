<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function getModuleData(Request $request)
    {
        if ($request->ajax()) {

            if ($request->check == 'solution') {
               $modules = Category::select('ID', 'ParentCategoryID', 'ParentCategoryCode','CategoryName','CategorySeq')
                    ->where('parentCategoryID', $request->ID)
                    ->where('CategoryType', 'Module')
                    ->orderBy('CategorySeq', 'asc')
                    ->distinct()
                    ->get()->toArray(); 
            }elseif ($request->check === 'module') {
                $modules = Category::select('ID','ParentCategoryID', 'ParentCategoryCode','CategoryName', 'CategorySeq')
                    ->where('parentCategoryID', $request->ID)
                    ->where('CategoryType', 'SubModule')
                    ->orderBy('CategorySeq', 'asc')
                    ->get()->toArray(); 
            }
            

            return response()->json($modules);
        }
    }
}
