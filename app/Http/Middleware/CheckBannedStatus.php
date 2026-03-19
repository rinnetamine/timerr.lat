<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckBannedStatus
{
    /**
     * handle an incoming request
     *
     */
    public function handle(Request $request, Closure $next): Response
    {
        // check if user is authenticated and banned
        if (Auth::check() && Auth::user()->isBanned()) {
            // logout the user immediately
            Auth::logout();
            
            // invalidate the session
            $request->session()->invalidate();
            
            // regenerate CSRF token
            $request->session()->regenerateToken();
            
            // redirect to login with error message
            return redirect()->route('login')
                ->with('error', 'Your account has been banned. Contact administrator for more information.');
        }
        
        return $next($request);
    }
}
