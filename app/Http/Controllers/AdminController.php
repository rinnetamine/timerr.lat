<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobSubmission;
use App\Models\User;
use App\Models\Transaction;
use App\Models\ContactMessage;
use App\Models\Job;
use App\Models\Message;
use App\Models\Review;
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
            
            return redirect('/profile')->with('success', 'Pieteikums apstiprināts un kredīti pārnesti pieteicējam.');
            
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

        $messages = ContactMessage::with('user')
            ->when(request('status') === 'unread', fn ($query) => $query->where('status', 'unread'))
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('admin.contact-messages', compact('messages'));
    }

    // delete a contact message
    public function deleteContact(ContactMessage $message)
    {
        $this->checkAdmin();

        try {
            $message->delete();
            return back()->with('success', 'Ziņojums dzēsts.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to delete contact message: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Neizdevās dzēst ziņojumu.']);
        }
    }

    // admin dashboard
    public function index()
    {
        $this->checkAdmin();

        $totalUsers = User::count();
        $newUsersWeek = User::where('created_at', '>=', now()->subDays(7))->count();
        $totalJobs = Job::count();
        $newJobsWeek = Job::where('created_at', '>=', now()->subDays(7))->count();
        $recentJobs = Job::latest()->take(5)->with('user')->get();
        $totalSubmissions = JobSubmission::count();
        $pendingAdmin = JobSubmission::where('status', JobSubmission::STATUS_ADMIN_REVIEW)->count();
        $activeDisputes = JobSubmission::whereIn('dispute_status', [
            JobSubmission::DISPUTE_REQUESTED,
            JobSubmission::DISPUTE_UNDER_REVIEW,
        ])->count();
        $resolvedDisputes = JobSubmission::where('dispute_status', JobSubmission::DISPUTE_RESOLVED)->count();
        $approvedSubmissions = JobSubmission::where('status', JobSubmission::STATUS_APPROVED)->count();
        $pendingSubmissions = JobSubmission::where('status', JobSubmission::STATUS_PENDING)->count();
        $claimedSubmissions = JobSubmission::where('status', JobSubmission::STATUS_CLAIMED)->count();
        $declinedSubmissions = JobSubmission::where('status', JobSubmission::STATUS_DECLINED)->count();
        $recentSignups = User::latest()->take(5)->get();
        $recentTransactions = Transaction::with('user')->latest()->take(6)->get();
        $contactMessagesCount = ContactMessage::count();
        $unreadMessagesCount = Message::whereNull('read_at')->count();
        $totalCredits = User::sum('time_credits');
        $creditMovement30Days = Transaction::where('created_at', '>=', now()->subDays(30))->sum('amount');
        $reviewsCount = Review::count();
        $averageRating = Review::avg('rating') ?? 0;
        $completionRate = $totalSubmissions > 0 ? ($approvedSubmissions / $totalSubmissions) * 100 : 0;
        $submissionsPerJob = $totalJobs > 0 ? $totalSubmissions / $totalJobs : 0;
        $jobsPerUser = $totalUsers > 0 ? $totalJobs / $totalUsers : 0;
        $topCategories = Job::query()
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'newUsersWeek',
            'totalJobs',
            'newJobsWeek',
            'recentJobs',
            'totalSubmissions',
            'pendingAdmin',
            'activeDisputes',
            'resolvedDisputes',
            'approvedSubmissions',
            'pendingSubmissions',
            'claimedSubmissions',
            'declinedSubmissions',
            'recentSignups',
            'recentTransactions',
            'contactMessagesCount',
            'unreadMessagesCount',
            'totalCredits',
            'creditMovement30Days',
            'reviewsCount',
            'averageRating',
            'completionRate',
            'submissionsPerJob',
            'jobsPerUser',
            'topCategories'
        ));
    }
}
