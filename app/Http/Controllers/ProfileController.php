<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $services = $user->jobs()->latest()->get();
        
        return view('auth.profile', [
            'user' => $user,
            'services' => $services
        ]);
    }
}
