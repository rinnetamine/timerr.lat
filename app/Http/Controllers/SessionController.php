<?php

// Šis fails apstrādā lietotāja pieslēgšanos, bloķēta konta pārbaudi un izrakstīšanos.

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    // Parāda pieslēgšanās formu.
    public function create()
    {
        return view('auth.login');
    }

    // Validē pieslēgšanās datus un izveido aktīvu lietotāja sesiju.
    public function store()
    {
        $attributes = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (! Auth::attempt($attributes)) {
            throw ValidationException::withMessages([
                'email' => 'Norādītie pieslēgšanās dati nav pareizi.'
            ]);
        }

        // Bloķēts lietotājs tiek izrakstīts uzreiz pēc veiksmīgas paroles pārbaudes.
        if (Auth::user()->isBanned()) {
            Auth::logout();
            
            throw ValidationException::withMessages([
                'email' => 'Jūsu konts ir bloķēts. Sazinieties ar administratoru, lai saņemtu vairāk informācijas.'
            ]);
        }

        // Sesijas atjaunošana pasargā pret sesijas fiksācijas uzbrukumiem.
        request()->session()->regenerate();

        return redirect('/jobs');
    }

    // Izraksta lietotāju no pašreizējās sesijas.
    public function destroy()
    {
        Auth::logout();

        return redirect('/');
    }
}
