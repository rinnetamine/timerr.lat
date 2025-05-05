<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

// controller for user registration
class RegisteredUserController extends Controller
{
    // show registration form
    public function create()
    {
        return view('auth.register');
    }

    // handle user registration
    public function store()
    {
        // validate registration input
        $attributes = request()->validate([
            'first_name' => ['required'],
            'last_name'  => ['required'],
            'email'      => ['required', 'email', 'unique:users'],
            'password'   => ['required', Password::min(6), 'confirmed']
        ]);

        // create new user
        $user = User::create($attributes);

        // log in the new user
        Auth::login($user);

        // redirect to jobs page
        return redirect('/jobs');
    }
}
