<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobSubmission;
use App\Models\User;
use App\Models\Transaction;
use App\Models\ContactMessage;
use App\Models\Job;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// admin controller for handling admin-specific actions
class AdminController extends Controller
{
    // check if current user has admin privileges
    private function checkAdmin()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }
    }
    
    // approve a job submission and transfer credits to the applicant
    public function approveSubmission(JobSubmission $submission)
    {
        $this->checkAdmin();
        
        // validate submission status
        if ($submission->status !== JobSubmission::STATUS_ADMIN_REVIEW) {
            return back()->withErrors([
                'error' => 'Šis iesniegums pašlaik nav administratora pārskatīšanā.'
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
                'description' => "Administrators apstiprināja palīdzību darbā: {$job->title}",
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
            
            return redirect('/profile')->with('success', 'Pieteikums apstiprinu0101ts un kredu012bti pu0101rnesti pieteiku0113jam.');
            
        } catch (\Exception $e) {
            // rollback transaction on error
            DB::rollBack();
            Log::error('Admin approval failed: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'Neizdevās apstiprināt iesniegumu: ' . $e->getMessage()
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
                'error' => 'Šis iesniegums pašlaik nav administratora pārskatīšanā.'
            ]);
        }
        
        try {
            // update submission status
            $submission->status = JobSubmission::STATUS_DECLINED;
            $submission->admin_approved = false;
            $submission->save();
            
            return redirect('/profile')->with('success', 'Iesniegums noraidīts. Darbs paliek pieejams citiem pieteicējiem.');
            
        } catch (\Exception $e) {
            Log::error('admin rejection failed: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'Neizdevās noraidīt iesniegumu: ' . $e->getMessage()
            ]);
        }
    }
    
    //show all contact messages (admin only)

    public function contactMessages()
    {
        $this->checkAdmin();

        $messages = ContactMessage::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.contact-messages', compact('messages'));
    }

    // delete a contact message
    public function deleteContact(ContactMessage $message)
    {
        $this->checkAdmin();

        try {
            $message->delete();
            return back()->with('success', 'Ziu0146ojums dzu0113sts.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to delete contact message: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Neizdevās dzēst ziņojumu.']);
        }
    }

    // admin dashboard
    public function index()
    {
        $this->checkAdmin();

        // core admin stats
        $totalUsers = User::count();
        $newUsersWeek = User::where('created_at', '>=', now()->subDays(7))->count();
        $totalJobs = Job::count();
        $recentJobs = Job::latest()->take(5)->with('user')->get();
        $totalSubmissions = JobSubmission::count();
        $pendingAdmin = JobSubmission::where('status', JobSubmission::STATUS_ADMIN_REVIEW)->count();
        $recentSignups = User::latest()->take(5)->get();
        $recentTransactions = Transaction::latest()->take(5)->get();
        $contactMessagesCount = ContactMessage::count();

        // get all items needing admin attention (disputes + admin reviews)
        $adminAttentionItems = JobSubmission::with(['user', 'jobListing', 'jobListing.user', 'disputeInitiator'])
            ->where(function($query) {
                $query->where('dispute_status', '!=', JobSubmission::DISPUTE_NONE)
                      ->orWhere('status', JobSubmission::STATUS_ADMIN_REVIEW);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard', compact(
            'adminAttentionItems',
            'totalUsers',
            'newUsersWeek',
            'totalJobs',
            'recentJobs',
            'totalSubmissions',
            'pendingAdmin',
            'recentSignups',
            'recentTransactions',
            'contactMessagesCount'
        ));
    }
}
