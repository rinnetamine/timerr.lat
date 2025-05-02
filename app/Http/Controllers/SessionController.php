<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

// handles user authentication and session management
class SessionController extends Controller
{
    // display the login form
    public function create()
    {
        return view('auth.login');
    }

    // process login attempt and create user session
    public function store()
    {
        $attributes = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (! Auth::attempt($attributes)) {
            throw ValidationException::withMessages([
                'email' => 'Sorry, those credentials do not match.'
            ]);
        }

        request()->session()->regenerate();

        return redirect('/jobs');
    }

    // log out the user and end their session
    public function destroy()
    {
        Auth::logout();

        return redirect('/');
    }
}
