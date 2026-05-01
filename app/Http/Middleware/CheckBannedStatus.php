<?php

// Šis fails pārtrauc bloķētu lietotāju sesijas pirms pieprasījums nonāk pie kontrolieriem.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckBannedStatus
{
    // Pārbauda, vai pieslēgtais lietotājs nav bloķēts, un vajadzības gadījumā izraksta viņu.
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isBanned()) {
            Auth::logout();
            
            // Sesija un CSRF marķieris tiek atjaunoti, lai bloķētais konts nevarētu turpināt darbību.
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('error', 'Jūsu konts ir bloķēts. Sazinieties ar administratoru, lai saņemtu vairāk informācijas.');
        }
        
        return $next($request);
    }
}
