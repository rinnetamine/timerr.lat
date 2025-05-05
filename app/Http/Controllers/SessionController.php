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
                'email' => 'Sorry, those credentials do not match.'
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
