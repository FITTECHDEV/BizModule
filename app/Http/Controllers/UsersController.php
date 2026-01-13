<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\MatchUserPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Alert;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('users-list')) {
            abort(404);
        }

        // $data = Cache::remember(
        //     'users.index',
        //     1800,
        //     function () {
        //    $data = User::with('roles')->latest()->paginate(300);
        //    $data = DB::table('Cust_User')->paginate(300);
        //    dd($data);
        // }
        // );

        return view('users.index');
        //$data = User::orderBy('id', 'DESC')->paginate(5);

    }

    /**
     * Get user statistics for dashboard cards
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        try {
            // Query the Cust_User table with CORRECT column name
            $stats = [
                'total' => DB::table('Cust_User')
                    ->where('IsDeleted', 0)  
                    ->count(),

                'active' => DB::table('Cust_User')
                    ->where('IsActive', 1)
                    ->where('IsDeleted', 0)  
                    ->count(),

                'inactive' => DB::table('Cust_User')
                    ->where('IsActive', 0)
                    ->where('IsDeleted', 0) 
                    ->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch user stats', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        $offices = Office::latest()->get();

        return view('users.create', compact('roles', 'offices'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'FirstName' => 'required',
            'LastName' => 'required',
            'Email' => 'required|email|unique:Cust_User,email',
            'OfficeID' => 'required',
            'password' => 'required|same:confirm-password',
            'roles' => 'required',
            'Role' => 'required'
        ]);

        $user = new User;

        $input = $request->all();
        $input['ID'] = guid();
        $input['password'] = Hash::make($request->password);
        $input['EmployeeCode'] = $user->generateUniqueEmployeeCode();
        $input['LoginID'] = $input['LoginID'];
        $input['OfficeCode'] = Office::select('Code')->where('ID', $input['OfficeID'])->first()->Code;
        $input['CreatedBy'] = Auth::user()->full_name;
        // $input['Role'] = $input['role'];
        // remove the roles from the array
        unset($input["roles"]);
        unset($input["confirm-password"]);
        // dd($request->input('roles'));
        // create user
        //    dd($input);
        $user_id = User::create($input);


        // assign roles
        $new_user = User::where('ID', $user_id->ID)->first();
        $new_user->assignRole($request->input('roles'));
        // notify users via mail

        // push notification
        toast('User created !!', 'success');

        // redirect to index
        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }
    public function updateAjax(Request $request)
    {
        // ddd($request);
        if ($request->ajax()) {
            User::where('ID', $request->pk)->first()
                ->update([
                    $request->name => $request->value
                ]);
            return response()->json(['success' => true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::where('ID', $id)->first();

        return view('users.profile', compact('user'));
    }
    public function GetChangePassword()
    {
        return view('user.changePassword');
    }
    /**
     * chage user password
     */
    public function ChangePassword(Request $request)
    {
        // return Hash::check('password', auth()->user()->password);
        if ($request->method() == 'POST') {

            $request->validate([
                'current_password' => ['required', new MatchUserPassword],
                'new_password' => ['required', 'different:current_password'],
                'new_confirm_password' => ['required', 'same:new_password'],
            ]);

            // $user = User::where('ID', Auth::user()->ID)->first();
            // dd($user);
            DB::table('Cust_User')->where('ID', Auth::user()->ID)->update([
                'password' => Hash::make($request->new_confirm_password),
                'PasswordResetAt' => Carbon::now()->toDateTimeString(),
                'lastModifiedBy' => Auth::user()->full_name
            ]);
            // $user->update([
            //     'password' => Hash::make($request->new_confirm_password),
            //     'PasswordResetAt' => Carbon::now()->toDateTimeString(),
            // ]);
            // dd($user);
            toast('Password updated !', 'success');

            Auth::logout();
            return redirect()->route('login');
        }


        return view('user.changePassword');
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::where("ID", $id)->first();
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();
        $offices = Office::latest()->get();

        return view('users.edit', compact('user', 'roles', 'userRole', 'offices'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'FirstName' => 'required',
            'LastName' => 'required',
            'Email' => 'required|email|unique:Cust_User,email,' . $id,
            'password' => 'same:confirm-password',
            'roles' => 'required',
            'OfficeID' => 'required',
            'LoginID' => 'required',
        ]);
        // dd($request);

        $input = $request->all();
        unset($input['_token'], $input['_method']);

        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, array('password'));
        }

        $input['OfficeCode'] = Office::select('Code')->where('ID', $input['OfficeID'])->first()->Code;

        // remove the roles from the array
        unset($input["roles"]);
        unset($input["confirm-password"]);

        //    dd($input);
        // update user
        $user = User::where('ID', $id)->first();
        User::where('ID', $id)->update($input);

        // TODO: notify user about the update

        // delete existing roles
        DB::table('Cust_Model_Has_Roles')
            ->where('model_uuid', $user->EmployeeCode)
            ->delete();
        //TODO: send mail for updated

        // assign rolePermissions
        $user->assignRole($request->input('roles'));

        // notify update
        toast('User Updated !!', 'success');

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }
    /**
     * Change user password on the flair //! Super Admin
     */
    public function pass(Request $request, User $user)
    {

        $this->validate($request, [
            'password' => 'required|same:confirm-password'
        ]);

        $input = $request->all();

        //remove confirm pass
        unset($input["confirm-password"]);
        //create hashed password
        $input['password'] = Hash::make($input['password']);
        //update password
        $user = User::where('ID', $user->ID)->first();
        $user->update($input);

        Alert::info('Password Updated !!');

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user =  User::where('ID', $id)->first();
        $user->delete();
        toast('User deleted successfully', 'success');
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }
}
