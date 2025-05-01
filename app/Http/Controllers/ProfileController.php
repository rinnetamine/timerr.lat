<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\JobSubmission;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        
        // Get user's services (jobs they created)
        $services = $user->jobs()->latest()->get();
        
        // Get submissions for jobs created by the user
        $receivedSubmissions = JobSubmission::whereHas('jobListing', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['jobListing', 'user'])->latest()->get();
        
        // Get submissions made by the user
        $sentSubmissions = JobSubmission::where('user_id', $user->id)
            ->with('jobListing.user')
            ->latest()
            ->get();
            
        $transactions = $user->transactions()->latest()->get();
        
        // Get admin review submissions if user is admin
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
