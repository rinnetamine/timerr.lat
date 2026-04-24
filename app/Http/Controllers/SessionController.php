<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

// controller for user authentication
class SessionController extends Controller
{
    // show login form
    public function create()
    {
        return view('auth.login');
    }

    // handle user login
    public function store()
    {
        // validate login credentials
        $attributes = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        // attempt to authenticate user
        if (! Auth::attempt($attributes)) {
            throw ValidationException::withMessages([
                'email' => 'Norādītie pieslēgšanās dati nav pareizi.'
            ]);
        }

        // check if user is banned after successful authentication
        if (Auth::user()->isBanned()) {
            // logout the banned user immediately
            Auth::logout();
            
            throw ValidationException::withMessages([
                'email' => 'Jūsu konts ir bloķēts. Sazinieties ar administratoru, lai saņemtu vairāk informācijas.'
            ]);
        }

        // regenerate session for security
        request()->session()->regenerate();

        // redirect to jobs page after successful login
        return redirect('/jobs');
    }

    // handle user logout
    public function destroy()
    {
        Auth::logout();

        return redirect('/');
    }
}
