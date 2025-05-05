<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// controller for handling contact form submissions
class ContactController extends Controller
{
    // show the contact form view
    public function index()
    {
        return view('contact');
    }

    // validates input, creates message, and saves to database
    public function store(Request $request)
    {
        // validate the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // create a new contact message with validated data
        $contactMessage = new ContactMessage($validated);
        
        // if user is logged in, associate the message with their account
        if (Auth::check()) {
            $contactMessage->user_id = Auth::id();
        }
        
        $contactMessage->save();

        // redirect back with success message
        return redirect()->back()->with('success', 'Your message has been sent successfully!');
    }
}
