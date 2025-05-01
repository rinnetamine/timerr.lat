<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobSubmission;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Check if the current user is an admin
     */
    private function checkAdmin()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
    }
    
    /**
     * Approve a submission from admin review and transfer credits to the applicant
     */
    public function approveSubmission(JobSubmission $submission)
    {
        // Check if user is admin
        $this->checkAdmin();
        
        // Ensure submission is in admin_review status
        if ($submission->status !== JobSubmission::STATUS_ADMIN_REVIEW) {
            return back()->withErrors([
                'error' => 'This submission is not in admin review status.'
            ]);
        }
        
        try {
            DB::beginTransaction();
            
            // Get the job and users involved
            $job = $submission->jobListing;
            $applicant = $submission->user;
            $jobCreator = $job->user;
            
            // Transfer credits from job to applicant
            $applicant->time_credits += $job->time_credits;
            $applicant->save();
            
            // Create a transaction record
            Transaction::create([
                'user_id' => $applicant->id,
                'amount' => $job->time_credits,
                'description' => "Admin approved: Credits for help with '{$job->title}'",
                'type' => 'credit'
            ]);
            
            // Update submission status
            $submission->status = JobSubmission::STATUS_APPROVED;
            $submission->admin_approved = true;
            $submission->save();
            
            // Delete the job as it's now completed
            $job->delete();
            
            DB::commit();
            
            return redirect('/profile')->with('success', 'Submission approved and credits transferred to applicant.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin approval failed: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'Failed to approve submission: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Reject a submission from admin review and return credits to the job creator
     */
    public function rejectSubmission(JobSubmission $submission)
    {
        // Check if user is admin
        $this->checkAdmin();
        
        // Ensure submission is in admin_review status
        if ($submission->status !== JobSubmission::STATUS_ADMIN_REVIEW) {
            return back()->withErrors([
                'error' => 'This submission is not in admin review status.'
            ]);
        }
        
        try {
            // Update submission status
            $submission->status = JobSubmission::STATUS_DECLINED;
            $submission->admin_approved = false;
            $submission->save();
            
            return redirect('/profile')->with('success', 'Submission rejected. The job remains available for others to claim.');
            
        } catch (\Exception $e) {
            Log::error('Admin rejection failed: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'Failed to reject submission: ' . $e->getMessage()
            ]);
        }
    }
}
