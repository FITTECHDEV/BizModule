<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Guid\Guid;

class CategoryController extends Controller
{
    public function index()
    {

        $solutions = Category::where('CategoryType', 'Solution')->get();
        $modules = Category::where('CategoryType', 'Module')->get();
        $submodules = Category::where('CategoryType', 'SubModule')->get();
        // dd($categories->where('CategoryType', 'Solution'));

        return view('categories.index', compact('solutions', 'modules', 'submodules'));
    }
    /**
     * get all category resources
     */
    public function getCategory($id = null, $type = null)
    {
        if (isset($type) && ($type == 'Solution')) {
            $categories = Category::select('ID', 'CategoryName', 'CategoryCode')
                ->orderby('CategorySeq', 'desc')
                ->where('ID', $id)
                ->first();

            return response()->json(['data' => $categories]);
        } elseif (isset($type) && ($type == 'Module')) {

            $categories = Category::select('ID', 'CategoryName', 'CategoryCode')
                ->where('ID', $id)
                ->orderby('CategorySeq', 'desc')
                ->first();

            return response()->json(['data' => $categories]);

        } elseif (isset($type) && ($type == 'SubModule')) {

            $categories = Category::select('ID', 'CategoryName', 'CategoryCode')
                ->where('ParentCategoryID', $id)
                ->orderby('CategorySeq', 'desc')
                ->first();

            return response()->json(['data' => $categories]);
        }
    }

    public function store(Request $request)
    {
        // return response()->json((isset($request->form_type) && ($request->form_type == "category")));
        //new Solution
        if ((isset($request->form_type) && ($request->form_type == "solution"))) {
            //create solution no parentCategoryID parentCategoryCode
            $new_solution_id = Category::create([
                'ID' => Guid(),
                'CategoryCode' => $request->SolutionCode,
                'CategoryName' => $request->SolutionName,
                'CategoryType' => 'Solution',
                'ParentCategoryID' => Null,
                'ParentCategoryCode' => Null,
                'CreatedBy' =>  Auth::user()->full_name,
                'LastModifiedBy' =>  Auth::user()->full_name,
            ]);
            //* create category
            // $new_category_id = Category::create([
            //     'ID' => guid(),
            //     'ParentCategoryID' => $new_solution_id->ID,
            //     'ParentCategoryCode' => $new_solution_id->CategoryCode,
            //     'CategoryCode' => $request->CategoryCode,
            //     'CategoryName' => $request->CategoryName,
            //     'CategoryType' => 'Module',
            //     'CreatedBy' =>  Auth::user()->full_name,
            //     'LastModifiedBy' =>  Auth::user()->full_name,
            // ]);
            //create sub category
            // Category::create([
            //     'ID' => guid(),
            //     'ParentCategoryID' => $new_category_id->ID,
            //     'ParentCategoryCode' => $new_category_id->CategoryCode,
            //     'CategoryCode' => $request->SubCategoryCode,
            //     'CategoryName' => $request->SubCategoryName,
            //     'CategoryType' => 'SubModule',
            //     'CreatedBy' =>  Auth::user()->full_name,
            //     'LastModifiedBy' =>  Auth::user()->full_name,
            // ]);

            return response()->json(['status' => true]);
        }
         elseif ((isset($request->form_type) && ($request->form_type == "category"))) {
            $new_category_id = Category::create([
                'ID' => guid(),
                'ParentCategoryID' => $request->oldSolutionID,
                'ParentCategoryCode' => $request->oldSolutionCode,
                'CategoryCode' => $request->CategoryCode,
                'CategoryName' => $request->CategoryName,
                'CategoryType' => 'Module',
                'CreatedBy' =>  Auth::user()->full_name,
                'LastModifiedBy' =>  Auth::user()->full_name,
            ]);
            //create sub category
            // Category::create([
            //     'ID' => guid(),
            //     'ParentCategoryID' => $new_category_id->ID,
            //     'ParentCategoryCode' => $new_category_id->CategoryCode,
            //     'CategoryCode' => $request->SubCategoryCode,
            //     'CategoryName' => $request->SubCategoryName,
            //     'CategoryType' => 'SubModule',
            //     'CreatedBy' =>  Auth::user()->full_name,
            //     'LastModifiedBy' =>  Auth::user()->full_name,
            // ]);

            return response()->json(['status' => true]);
        } else {

            $newSubmodule = Category::create([
                'ID' => guid(),
                // TODO: get the correct request parameter for parentcategorycode
                'ParentCategoryID' => $request->SolutionCode,
                'ParentCategoryCode' => $request->CategoryCode,
                'CategoryCode' => $request->SubCategoryCode,
                'CategoryName' => $request->SubCategoryName,
                'CategoryType' => 'SubModule',
                'CreatedBy' =>  Auth::user()->full_name,
                'LastModifiedBy' =>  Auth::user()->full_name,
            ]);

            return response()->json(['status' => 'success', 'data'=>$newSubmodule]);
        }
        return response()->json(['status' => false]);
    }

    public function subModuleUpdateAjax(Request $request)
    {
        // dd($request);
        if ($request->ajax()) {
            $res = DB::table('Cust_Category')->where('ID', $request->pk)->update([
                $request->name => ucwords($request->value)
            ]);
            // $cat = Category::where('ID', $request->pk)->first();
            //     $res = $cat->update([
            //         $request->name => $request->value
            //     ]);
            return response()->json(['success' =>    $res]);
        }
    }
}
