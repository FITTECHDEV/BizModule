<?php

namespace App\Http\Controllers\API\v1\Auth;

// use Adldap\Laravel\Facades\Adldap;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'LoginID' => '',
            'Password' => ''
        ]);



        $username = (String) $validated['LoginID'];
        $Password = $validated['Password'];

        $user = User::where('LoginID',$username)->first(); //  check if user exists on the system


        $permissions = ['Import', 'Billing', 'Admin', 'Manager', 'G4S'];


        $res_401 = "401:";

        if ($user) {
            if (Hash::check($Password, $user->password)) {

                if (!$user->hasAnyPermission($permissions)) {
                    return ($res_401);
                }

                $permission = $this->getPermission($user->getAllPermissions());
                $res_200 = "200:" . $permission;
                Log::notice("User: " . $user->LoginID . " OPS Local DB Login");
                return ($res_200);

            } else {
                return ($res_401);
            }
        }



        // AD Login
        // if (Adldap::auth()->attempt($username, $Password, $bindAsUser = true)) {

        //     $user = User::where('LoginID', $username)->first(); //  check if user exists on the system

        //     if (!$user->hasAnyPermission($permissions)) {

        //         return ($res_401);
        //     }

        //     $permission = $this->getPermission($user->getAllPermissions());
        //     $res_200 = "200:" . $permission;
        //     Log::notice("User: ".$user->LoginID." OPS AD Login");

        //     return ($res_200);
        // } else {

        //     $user = User::where('LoginID', $username)->first(); //  check if user exists on the system

        //     if ($user) {
        //         if (Hash::check($Password, $user->password)) {
        //             // return $user->getAllPermissions();
        //             if (!$user->hasAnyPermission($permissions)) {
        //                 return ($res_401);
        //             }
        //             $permission = $this->getPermission($user->getAllPermissions());
        //             $res_200 = "200:" . $permission;
        //             Log::notice("User: ".$user->LoginID." OPS Local DB Login");
        //             return ($res_200);
        //         } else {
        //             return ($res_401);
        //         }
        //     } else {
        //         return ($res_401);
        //     }
        // }

        return ($res_401);
    }

    function getPermission($permissions)
    {
        $results = '';
        foreach ($permissions as $permission) {
            switch ($permission->name) {
                case 'Import':
                    $results = 'Import';
                    break;
                case 'Billing':
                    $results = 'Billing';
                    break;
                case 'Admin':
                    $results = 'Admin';
                    break;
                case 'Manager':
                    $results = 'Manager';
                    break;
                case 'G4S':
                    $results = 'G4S';
                    break;
            }
        }

        return $results;
    }
}
