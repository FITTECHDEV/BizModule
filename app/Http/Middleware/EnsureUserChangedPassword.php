<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserChangedPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {   
        
        if (auth()->user()->PasswordResetAt == null) {
            toast('Kindly change Your password', 'info');
            return redirect()->route('user.get_change_password');
        }
        return $next($request);
    }
}
