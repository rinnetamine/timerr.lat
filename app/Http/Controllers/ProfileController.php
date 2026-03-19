<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\JobSubmission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

// controller for user profile and dashboard
class ProfileController extends Controller
{
    // display user profile with all related data
    public function show()
    {
        $user = auth()->user();
        
        // load stats for display
        $user->loadCount(['jobs', 'completedJobs', 'reviewsReceived']);
        $user->load(['reviewsReceived.reviewer']);
        
        // add average rating calculation
        $user->reviews_received_rating_avg = \App\Models\Review::where('reviewee_id', $user->id)->avg('rating') ?? 0;
        
        // fetch user's job listings
        $services = $user->jobs()->latest()->get();
        
        // fetch submissions for user's jobs
        $receivedSubmissions = JobSubmission::whereHas('jobListing', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['jobListing', 'user'])->latest()->get();
        
        // fetch user's job submissions
        $sentSubmissions = JobSubmission::where('user_id', $user->id)
            ->with('jobListing.user')
            ->latest()
            ->get();
            
        // fetch user's transaction history
        $transactions = $user->transactions()->latest()->get();
        
        // fetch admin review submissions if user is admin
        $adminReviewSubmissions = [];
        if ($user->isAdmin()) {
            $adminReviewSubmissions = JobSubmission::where('status', JobSubmission::STATUS_ADMIN_REVIEW)
                ->with(['jobListing', 'user', 'jobListing.user'])
                ->latest()
                ->get();
        }
        
        return view('auth.profile', [
            'user' => $user,
            'services' => $services,
            'receivedSubmissions' => $receivedSubmissions,
            'sentSubmissions' => $sentSubmissions,
            'transactions' => $transactions,
            'adminReviewSubmissions' => $adminReviewSubmissions
        ]);
    }

    // change user password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required', 
                'string', 
                'confirmed', 
                'min:6',
                'regex:/[0-9]/', // at least one number
                'regex:/[!@#$%^&*()_+=\-\[\]{};:\'"<>,\.?\/]/' // at least one special character
            ]
        ]);

        $user = auth()->user();

        // verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.'
            ]);
        }

        // update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password changed successfully!');
    }
}
