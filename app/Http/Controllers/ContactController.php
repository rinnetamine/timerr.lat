<?php

// Šis fails apstrādā publisko kontaktformu un saglabā ziņojumus administratora pārskatīšanai.

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    // Parāda kontaktformas skatu.
    public function index()
    {
        return view('contact');
    }

    // Validē ievadi, piesaista lietotāju, ja tas ir pieslēdzies, un saglabā kontaktziņojumu.
    public function store(Request $request)
    {
        // Ienākošie dati tiek ierobežoti, lai kontaktziņojumi paliktu īsi un droši glabājami.
        $validated = $request->validate([
            'name' => 'required|string|max:60',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:120',
            'message' => 'required|string|max:1000',
        ]);

        $contactMessage = new ContactMessage($validated);
        
        // Pieslēgta lietotāja ziņojumu vēlāk var saistīt ar viņa profilu administratora panelī.
        if (Auth::check()) {
            $contactMessage->user_id = Auth::id();
        }
        
        $contactMessage->save();

        return redirect()->back()->with('success', 'Jūsu ziņojums ir nosūtīts veiksmīgi!');
    }
}
