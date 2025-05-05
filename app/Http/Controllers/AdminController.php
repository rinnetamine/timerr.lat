<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobSubmission;
use App\Models\User;
use App\Models\Transaction;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// admin controller for handling admin-specific actions
class AdminController extends Controller
{
    // check if current user has admin privileges
    private function checkAdmin()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
    }
    
    // approve a job submission and transfer credits to the applicant
    public function approveSubmission(JobSubmission $submission)
    {
        $this->checkAdmin();
        
        // validate submission status
        if ($submission->status !== JobSubmission::STATUS_ADMIN_REVIEW) {
            return back()->withErrors([
                'error' => 'This submission is not in admin review status.'
            ]);
        }
        
        try {
            // start database transaction
            DB::beginTransaction();
            
            // get job and user information
            $job = $submission->jobListing;
            $applicant = $submission->user;
            $jobCreator = $job->user;
            
            // transfer credits to applicant
            $applicant->time_credits += $job->time_credits;
            $applicant->save();
            
            // log transaction
            Transaction::create([
                'user_id' => $applicant->id,
                'amount' => $job->time_credits,
                'description' => "Admin approved: Credits for help with '{$job->title}'",
                'type' => 'credit'
            ]);
            
            // update submission status
            $submission->status = JobSubmission::STATUS_APPROVED;
            $submission->admin_approved = true;
            $submission->save();
            
            // complete job by deleting it
            $job->delete();
            
            // commit transaction
            DB::commit();
            
            return redirect('/profile')->with('success', 'Submission approved and credits transferred to applicant.');
            
        } catch (\Exception $e) {
            // rollback transaction on error
            DB::rollBack();
            Log::error('Admin approval failed: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'Failed to approve submission: ' . $e->getMessage()
            ]);
        }
    }
    
    // reject a job submission and keep job open
    public function rejectSubmission(JobSubmission $submission)
    {
        // verify admin access
        $this->checkAdmin();
        
        // validate submission status
        if ($submission->status !== JobSubmission::STATUS_ADMIN_REVIEW) {
            return back()->withErrors([
                'error' => 'This submission is not in admin review status.'
            ]);
        }
        
        try {
            // update submission status
            $submission->status = JobSubmission::STATUS_DECLINED;
            $submission->admin_approved = false;
            $submission->save();
            
            return redirect('/profile')->with('success', 'submission rejected. the job remains available for others to claim.');
            
        } catch (\Exception $e) {
            Log::error('admin rejection failed: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'failed to reject submission: ' . $e->getMessage()
            ]);
        }
    }
    
    //show all contact messages (admin only)

    public function contactMessages()
    {
        $messages = ContactMessage::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.contact-messages', compact('messages'));
    }
}
