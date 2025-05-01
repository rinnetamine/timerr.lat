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
    public function contactMessages()
    {
        // check if user is admin
        $this->checkAdmin();
        
        // get all messages, newest first
        $messages = ContactMessage::latest()->get();
        
        return view('auth.profile', [
            'activeTab' => 'contact-messages',
            'messages' => $messages,
            'user' => auth()->user(),
            'services' => auth()->user()->jobs()->latest()->get(),
            'receivedSubmissions' => JobSubmission::whereHas('jobListing', function ($query) {
                $query->where('user_id', auth()->id());
            })->with(['jobListing', 'user'])->latest()->get(),
            'sentSubmissions' => JobSubmission::where('user_id', auth()->id())
                ->with('jobListing.user')
                ->latest()
                ->get(),
            'adminReviewSubmissions' => JobSubmission::where('status', JobSubmission::STATUS_ADMIN_REVIEW)
                ->with(['jobListing', 'user'])
                ->latest()
                ->get(),
            'transactions' => auth()->user()->transactions()->latest()->get()
        ]);
    }
    
    /**
     * Delete a contact message
     */
    public function deleteContactMessage(ContactMessage $message)
    {
        // check if user is admin
        $this->checkAdmin();
        
        try {
            $message->delete();
            return back()->with('success', 'Message deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Contact message deletion failed: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'Failed to delete message: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Mark a contact message as read
     */
    public function markContactMessageAsRead(ContactMessage $message)
    {
        // check if user is admin
        $this->checkAdmin();
        
        try {
            $message->update(['is_read' => true]);
            return back()->with('success', 'Message marked as read.');
        } catch (\Exception $e) {
            Log::error('Contact message update failed: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'Failed to update message: ' . $e->getMessage()
            ]);
        }
    }
}
