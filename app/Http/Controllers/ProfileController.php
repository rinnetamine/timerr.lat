<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\JobSubmission;

// controller for user profile and dashboard
class ProfileController extends Controller
{
    // display user profile with all related data
    public function show()
    {
        $user = auth()->user();
        
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
}
