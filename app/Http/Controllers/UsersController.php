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
use Illuminate\Support\Facades\Log;

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

        return view('users.index');
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
        try {
            $this->validate($request, [
                'FirstName' => 'required|string|max:255',
                'LastName' => 'required|string|max:255',
                'Email' => 'required|email|unique:Cust_User,email',
                'OfficeID' => 'required|exists:offices,ID',
                'password' => 'required|same:confirm-password|min:8',
                'roles' => 'required|array',
                'Role' => 'required|string',
                'LoginID' => 'required|string|max:255',
            ]);

            $input = $request->all();
            $input['ID'] = Str::uuid()->toString(); // Replace guid() with Str::uuid()
            $input['password'] = Hash::make($request->password);
            $input['EmployeeCode'] = (new User)->generateUniqueEmployeeCode();
            $input['OfficeCode'] = Office::where('ID', $input['OfficeID'])->value('Code');
            $input['CreatedBy'] = Auth::user()->full_name;

            unset($input['roles'], $input['confirm-password']);

            $user = User::create($input);
            $user->assignRole($request->input('roles'));

            toast('User created !!', 'success');
            return redirect()->route('admin.users.index')->with('success', 'User created successfully');
        } catch (\Exception $e) {
            Log::error('Error storing user in UsersController', [
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An unexpected error occurred.');
        }
    }

    /**
     * Update user via AJAX.
     */
    public function updateAjax(Request $request)
    {
        if ($request->ajax()) {
            try {
                if (!Str::isUuid($request->pk)) {
                    Log::warning('Invalid UUID in updateAjax', ['id' => $request->pk]);
                    return response()->json(['success' => false, 'message' => 'Invalid user ID'], 400);
                }

                $user = User::where('ID', $request->pk)->first();
                if (!$user) {
                    Log::warning('User not found in updateAjax', ['id' => $request->pk]);
                    return response()->json(['success' => false, 'message' => 'User not found'], 404);
                }

                $user->update([$request->name => $request->value]);
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                Log::error('Error in updateAjax', [
                    'id' => $request->pk,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
            }
        }
        return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        Log::info('UsersController::show called', [
            'id' => $id,
            'request_url' => request()->fullUrl(),
        ]);

        try {
            if (!Str::isUuid($id)) {
                Log::warning('Invalid UUID format in UsersController::show', ['id' => $id]);
                return response()->view('errors.404', [], 404);
            }

            $user = User::where('ID', $id)->first();
            if (!$user) {
                Log::warning('User not found in UsersController::show', ['id' => $id]);
                return response()->view('errors.404', [], 404);
            }

            return view('users.profile', compact('user'));
        } catch (\Exception $e) {
            Log::error('Error in UsersController::show', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->view('errors.500', [], 500);
        }
    }

    /**
     * Show the form to change user password.
     */
    public function GetChangePassword()
    {
        return view('user.changePassword');
    }

    /**
     * Change user password.
     */
    public function ChangePassword(Request $request)
    {
        if ($request->method() == 'POST') {
            try {
                $request->validate([
                    'current_password' => ['required', new MatchUserPassword],
                    'new_password' => ['required', 'different:current_password', 'min:8'],
                    'new_confirm_password' => ['required', 'same:new_password'],
                ]);

                $user = User::where('ID', Auth::user()->ID)->first();
                if (!$user) {
                    Log::warning('User not found in ChangePassword', ['id' => Auth::user()->ID]);
                    return redirect()->back()->with('error', 'User not found.');
                }

                $user->update([
                    'password' => Hash::make($request->new_confirm_password),
                    'PasswordResetAt' => Carbon::now()->toDateTimeString(),
                    'lastModifiedBy' => Auth::user()->full_name,
                ]);

                toast('Password updated!', 'success');
                Auth::logout();
                return redirect()->route('login');
            } catch (\Exception $e) {
                Log::error('Error in ChangePassword', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return redirect()->back()->with('error', 'An unexpected error occurred.');
            }
        }

        return view('user.changePassword');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            if (!Str::isUuid($id)) {
                Log::warning('Invalid UUID format in UsersController::edit', ['id' => $id]);
                return response()->view('errors.404', [], 404);
            }

            $user = User::where('ID', $id)->first();
            if (!$user) {
                Log::warning('User not found in UsersController::edit', ['id' => $id]);
                return response()->view('errors.404', [], 404);
            }

            $roles = Role::pluck('name', 'name')->all();
            $userRole = $user->roles->pluck('name', 'name')->all();
            $offices = Office::latest()->get();

            return view('users.edit', compact('user', 'roles', 'userRole', 'offices'));
        } catch (\Exception $e) {
            Log::error('Error in UsersController::edit', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->view('errors.500', [], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'FirstName' => 'required|string|max:255',
                'LastName' => 'required|string|max:255',
                'Email' => 'required|email|unique:Cust_User,email,' . $id . ',ID',
                'password' => 'nullable|same:confirm-password|min:8',
                'roles' => 'required|array',
                'OfficeID' => 'required|exists:offices,ID',
                'LoginID' => 'required|string|max:255',
            ]);

            $input = $request->all();
            unset($input['_token'], $input['_method'], $input['confirm-password']);

            if (!empty($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            } else {
                $input = Arr::except($input, ['password']);
            }

            $input['OfficeCode'] = Office::where('ID', $input['OfficeID'])->value('Code');

            unset($input['roles']);

            $user = User::where('ID', $id)->first();
            if (!$user) {
                Log::warning('User not found in UsersController::update', ['id' => $id]);
                return redirect()->route('admin.users.index')->with('error', 'User not found.');
            }

            $user->update($input);

            DB::table('Cust_Model_Has_Roles')
                ->where('model_uuid', $user->EmployeeCode)
                ->delete();

            $user->assignRole($request->input('roles'));

            toast('User Updated !!', 'success');
            return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            Log::error('Error in UsersController::update', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An unexpected error occurred.');
        }
    }

    /**
     * Change user password on the flair (Super Admin).
     */
    public function pass(Request $request, User $user)
    {
        try {
            $this->validate($request, [
                'password' => 'required|same:confirm-password|min:8',
            ]);

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            Alert::info('Password Updated !!');
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Error in UsersController::pass', [
                'user_id' => $user->ID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An unexpected error occurred.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            if (!Str::isUuid($id)) {
                Log::warning('Invalid UUID format in UsersController::destroy', ['id' => $id]);
                return redirect()->route('admin.users.index')->with('error', 'Invalid user ID.');
            }

            $user = User::where('ID', $id)->first();
            if (!$user) {
                Log::warning('User not found in UsersController::destroy', ['id' => $id]);
                return redirect()->route('admin.users.index')->with('error', 'User not found.');
            }

            $user->delete();
            toast('User deleted successfully', 'success');
            return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error in UsersController::destroy', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An unexpected error occurred.');
        }
    }
}