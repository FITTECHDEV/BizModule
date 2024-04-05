<?php

namespace App\Http\Controllers\Auth;

use Adldap\Laravel\Facades\Adldap;
use Adldap\Laravel\Traits\HasLdapUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, HasLdapUser;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout()
    {

        Auth::logout();
        if (config('app.env') == 'Local'){
            return Redirect::to('/login');
        }
        return Redirect::to('/logout/azure');
    }

    /**
     * Get the loginID to be used by the controller.
     *
     * @return string
     */
    // public function username()
    // {
    //     return 'LoginID';
    // }

    // protected function validateLogin(Request $request)
    // {
    //     $this->validate($request, [
    //         $this->username() => 'required|string',
    //         'password' => 'required|string',
    //     ]);
    // }

//     protected function attemptLogin(Request $request)
//     {
//         $credentials = $request->only($this->username(), 'password');
//         $username = $credentials[$this->username()] . "@freight-in-time.com";
//         $password = $credentials['password'];

//         $user_format = env('LDAP_USER_FORMAT', env('LDAP_BASE_DN', ''));
//         $userdn = sprintf($user_format, $username);
//         //        dd($user_format,$userdn);
//         // you might need this, as reported in
//         // [#14](https://github.com/jotaelesalinas/laravel-simple-ldap-auth/issues/14):
//         // Adldap::auth()->bind($userdn, $password);



//         if (Adldap::auth()->attempt($username, $password, $bindAsUser = true)) {
//             //            if(Adldap::auth()->attempt($userdn, $password, $bindAsUser = true)) {
//             // the user exists in the LDAP server, with the provided password

//             $user = User::where('LoginID', $request->LoginID)->first();

//             if (!$user) {
//                 Alert::alert('Error', 'Kindly Contact IT Support !!', 'error')->persistent(false, true);
//                 return false;
//             }
//             //            if (!$user) {
// //                // the user doesn't exist in the local database, so we have to create one
// //
// //                $user = new \App\User();
// //                $user->username = $username;
// //                $user->password = '';
// //
// //                // you can skip this if there are no extra attributes to read from the LDAP server
// //                // or you can move it below this if(!$user) block if you want to keep the user always
// //                // in sync with the LDAP server
// //                $sync_attrs = $this->retrieveSyncAttributes($username . env('LDAP_ACCOUNT_SUFFIX'));
// //
// //                foreach ($sync_attrs as $field => $value) {
// //                    $user->$field = $value !== null ? $value : '';
// //                }
// //            }
// //

//             // by logging the user we create the session, so there is no need to login again (in the configured time).
//             // pass false as second parameter if you want to force the session to expire when the user closes the browser.
//             // have a look at the section 'session lifetime' in `config/session.php` for more options.
//             $this->guard('web')->login($user, true);

//             return true;
//         } else {
//             $user = User::where('LoginID', $request->LoginID)->first();
//             if ($user && Hash::check($password, $user->password)) {
//                 $this->guard('web')->login($user, true);
//                 return true;
//             } else {
//                 Alert::alert('Error', 'Kindly Contact IT Support For Help !!', 'error')->persistent(false, true);
//                 return false;
//             }
//         }

//         // the user doesn't exist in the LDAP server or the password is wrong
//         // log error
//         return false;
//     }

    // protected function retrieveSyncAttributes($username)
    // {
    //     $ldapuser = Adldap::search()->findBy('userprincipalname', $username);
    //     if (!$ldapuser) {
    //         // log error
    //         return false;
    //     }
    //     // if you want to see the list of available attributes in your specific LDAP server:
    //     // var_dump($ldapuser->attributes); exit;

    //     // needed if any attribute is not directly accessible via a method call.
    //     // attributes in \Adldap\Models\User are protected, so we will need
    //     // to retrieve them using reflection.
    //     $ldapuser_attrs = null;

    //     $attrs = [];

    //     foreach (config('ldap_auth.sync_attributes') as $local_attr => $ldap_attr) {
    //         if ($local_attr == 'username') {
    //             continue;
    //         }

    //         $method = 'get' . $ldap_attr;
    //         if (method_exists($ldapuser, $method)) {
    //             $attrs[$local_attr] = $ldapuser->$method();
    //             continue;
    //         }

    //         if ($ldapuser_attrs === null) {
    //             $ldapuser_attrs = self::accessProtected($ldapuser, 'attributes');
    //         }

    //         if (!isset($ldapuser_attrs[$ldap_attr])) {
    //             // an exception could be thrown
    //             $attrs[$local_attr] = null;
    //             continue;
    //         }

    //         if (!is_array($ldapuser_attrs[$ldap_attr])) {
    //             $attrs[$local_attr] = $ldapuser_attrs[$ldap_attr];
    //         }

    //         if (count($ldapuser_attrs[$ldap_attr]) == 0) {
    //             // an exception could be thrown
    //             $attrs[$local_attr] = null;
    //             continue;
    //         }

    //         // now it returns the first item, but it could return
    //         // a comma-separated string or any other thing that suits you better
    //         $attrs[$local_attr] = $ldapuser_attrs[$ldap_attr][0];
    //         //$attrs[$local_attr] = implode(',', $ldapuser_attrs[$ldap_attr]);
    //     }

    //     return $attrs;
    // }

    // protected static function accessProtected($obj, $prop)
    // {
    //     $reflection = new \ReflectionClass($obj);
    //     $property = $reflection->getProperty($prop);
    //     $property->setAccessible(true);
    //     return $property->getValue($obj);
    // }

}
