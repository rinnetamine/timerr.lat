<?php

// Šis fails apstrādā jauna lietotāja reģistrāciju un sākotnējo pieslēgšanu sistēmai.

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    // Parāda reģistrācijas formu.
    public function create()
    {
        return view('auth.register');
    }

    // Reģistrē jaunu lietotāju, pārbauda paroli un pēc saglabāšanas uzreiz pieslēdz kontu.
    public function store()
    {
        $attributes = request()->validate([
            // Visi galvenie reģistrācijas lauki ir obligāti, jo tie veido publisko profilu.
            'first_name' => ['required', 'string', 'max:30'],
            'last_name'  => ['required', 'string', 'max:30'],
            'email'      => ['required', 'email', 'max:255', 'unique:users'],
            'password'   => [
                'required', 
                'min:6', 
                'confirmed',
                'regex:/[0-9]/', // Parolē jābūt vismaz vienam ciparam.
                'regex:/[!@#$%^&*()_+=\-\[\]{};:\'"<>,\.?\/]/' // Parolē jābūt vismaz vienam speciālajam simbolam.
            ]
        ]);

        $user = User::create($attributes);

        Auth::login($user);

        return redirect('/jobs');
    }
}
