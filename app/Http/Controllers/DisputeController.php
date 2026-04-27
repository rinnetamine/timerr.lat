<?php

namespace App\Http\Controllers;

use App\Models\JobSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
    /**
     * show the dispute form for a job submission
     */
    public function create(JobSubmission $submission)
    {
        // check if user is involved in this job
        $user = Auth::user();
        if ($submission->user_id !== $user->id && $submission->jobListing->user_id !== $user->id) {
            return back()->with('error', 'Jums nav tiesību strīdēties par šo iesniegumu.');
        }

        return view('disputes.create', [
            'submission' => $submission->load(['jobListing', 'user'])
        ]);
    }

    /**
     * store a new dispute
     */
    public function store(Request $request, JobSubmission $submission)
    {
        // validate request
        $request->validate([
            'reason' => 'required|string|min:10|max:1000'
        ]);

        // check if user is involved in this job
        $user = Auth::user();
        if ($submission->user_id !== $user->id && $submission->jobListing->user_id !== $user->id) {
            return back()->with('error', 'Jums nav tiesību strīdēties par šo iesniegumu.');
        }

        // create dispute and freeze submission
        $submission->update([
            'dispute_status' => JobSubmission::DISPUTE_REQUESTED,
            'dispute_reason' => $request->reason,
            'dispute_initiated_by' => $user->id,
            'is_frozen' => true,
            'freeze_reason' => 'Strīdu uzsāka ' . $user->first_name . ' ' . $user->last_name
        ]);

        return redirect()->route('submissions.show', $submission)
            ->with('success', 'Strīds iesniegts. Administrators to pārskatīs drīz.');
    }

    /**
     * show admin dispute management page
     */
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $disputes = JobSubmission::with(['user', 'jobListing.user', 'disputeInitiator', 'disputeResolver', 'files'])
            ->where(function($query) {
                // show disputes and admin reviews
                $query->where('dispute_status', '!=', JobSubmission::DISPUTE_NONE)
                      ->orWhere('status', JobSubmission::STATUS_ADMIN_REVIEW);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('disputes.index', compact('disputes'));
    }

    /**
     * show dispute details for admin
     */
    public function show(JobSubmission $submission)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $submission->load(['user', 'jobListing.user', 'disputeInitiator', 'disputeResolver', 'files']);
        
        return view('disputes.show', compact('submission'));
    }

    /**
     * resolve a dispute (admin only)
     */
    public function resolve(Request $request, JobSubmission $submission)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'resolution' => 'required|string|min:10|max:1000',
            'action' => 'required|in:approve,decline,unfreeze'
        ]);

        // update submission with resolution
        $submission->update([
            'dispute_status' => JobSubmission::DISPUTE_RESOLVED,
            'dispute_resolution' => $request->resolution,
            'dispute_resolved_by' => Auth::id(),
            'dispute_resolved_at' => now()
        ]);

        // apply the chosen action
        switch ($request->action) {
            case 'approve':
                $submission->update([
                    'status' => JobSubmission::STATUS_APPROVED,
                    'admin_approved' => true,
                    'is_frozen' => false,
                    'freeze_reason' => null
                ]);
                
                // transfer credits
                $submission->user->increment('time_credits', $submission->jobListing->time_credits);
                $submission->jobListing->user->decrement('time_credits', $submission->jobListing->time_credits);
                
                // create transaction records
                \App\Models\Transaction::create([
                    'user_id' => $submission->user_id,
                    'amount' => $submission->jobListing->time_credits,
                    'description' => "Pabeigts darbs: {$submission->jobListing->title}"
                ]);

                \App\Models\Transaction::create([
                    'user_id' => $submission->jobListing->user_id,
                    'amount' => -$submission->jobListing->time_credits,
                    'description' => "Samaksāts par pabeigtu darbu: {$submission->jobListing->title}"
                ]);
                break;

            case 'decline':
                $submission->update([
                    'status' => JobSubmission::STATUS_DECLINED,
                    'admin_approved' => false,
                    'is_frozen' => false,
                    'freeze_reason' => null
                ]);
                break;

            case 'unfreeze':
                $submission->update([
                    'is_frozen' => false,
                    'freeze_reason' => null
                ]);
                break;
        }

        return redirect()->route('disputes.index')
            ->with('success', 'Strīds veiksmīgi atrisināts.');
    }
}
