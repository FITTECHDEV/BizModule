<?php

namespace App\Http\Controllers;

use App\Models\Logintracker\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Redirect to Azure AD for authentication.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToAzure()
    {
        return Socialite::driver('azure')->redirect();
    }

    /**
     * Handle Azure AD callback and log in user.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function handleAzureCallback()
    {
        try {
            $azureUser = Socialite::driver('azure')->user();
            $user = User::findByIdentifier($azureUser->email); // Use email as userPrincipalName

            if (!$user) {
                $user = User::create([
                    'id' => $azureUser->id,
                    'userPrincipalName' => $azureUser->email,
                    'displayName' => $azureUser->name,
                    'mail' => $azureUser->email,
                    // Add other fields as needed
                ]);
            }

            Auth::guard('azure')->login($user);
            \Log::info('User logged in: ' . $user->id);
            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            \Log::error('Azure AD login failed: ' . $e->getMessage());
            return response()->json(['error' => 'Authentication failed'], 401);
        }
    }

    /**
     * Log out the user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('azure')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logged out']);
    }
}
?>