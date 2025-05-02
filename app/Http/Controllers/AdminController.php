<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobSubmission;
use App\Models\User;
use App\Models\Transaction;
use App\Models\ContactMessage;
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
            // start a database transaction so that all related updates either all succeed or all fail
            DB::beginTransaction();
            
            // Get the job and users involved
            $job = $submission->jobListing;
            $applicant = $submission->user;
            $jobCreator = $job->user;
            
            // transfer the reserved credits from the job to the applicant
            $applicant->time_credits += $job->time_credits;
            $applicant->save();
            
            // create an audit transaction record
            Transaction::create([
                'user_id' => $applicant->id,
                'amount' => $job->time_credits,
                'description' => "Admin approved: Credits for help with '{$job->title}'",
                'type' => 'credit'
            ]);
            
            // mark submission as approved by admin
            $submission->status = JobSubmission::STATUS_APPROVED;
            $submission->admin_approved = true;
            $submission->save();
            
            // delete the job because it has been completed
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
            // update submission status to declined (admin decision) but keep job open
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
    
    /**
     * Show all contact messages (admin only)
     */
    
    
    /**
     * Delete a contact message
     */
    
    
    /**
     * Mark a contact message as read
     */

}
