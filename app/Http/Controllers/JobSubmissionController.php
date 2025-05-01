<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobSubmission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class JobSubmissionController extends Controller
{
    /**
     * Download a submission file securely
     */
    public function downloadFile(SubmissionFile $file)
    {
        // Check if user is authorized to download this file
        $submission = $file->jobSubmission;
        
        // Allow download if user is either:
        // 1. The job author, or
        // 2. The applicant who uploaded the file
        if (auth()->id() !== $submission->jobListing->user_id && auth()->id() !== $submission->user_id) {
            abort(403, 'You are not authorized to download this file.');
        }
        
        // Check if file exists in storage
        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'File not found.');
        }
        
        // Get the file from storage
        $path = Storage::disk('public')->path($file->file_path);
        $content = file_get_contents($path);
        $filename = $file->file_name;
        
        // Return file as download response
        return Response::make($content, 200, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
    public function claim()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $attributes = request()->validate([
            'job_id' => ['required', 'exists:job_listings,id'],
        ]);

        $job = Job::findOrFail($attributes['job_id']);

        // Check if user is trying to apply to their own job
        if (auth()->id() === $job->user_id) {
            return back()->withErrors([
                'error' => 'You cannot apply to your own help request.'
            ]);
        }

        // Check if this job is already claimed by someone else
        $existingClaim = JobSubmission::where('job_listing_id', $job->id)
            ->whereIn('status', [JobSubmission::STATUS_CLAIMED, JobSubmission::STATUS_PENDING, JobSubmission::STATUS_APPROVED])
            ->exists();

        if ($existingClaim) {
            return back()->withErrors([
                'error' => 'This help request has already been claimed by another user.'
            ]);
        }

        // Check if user has already claimed or applied to this job
        $userSubmission = JobSubmission::where('job_listing_id', $job->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($userSubmission) {
            // If the user already has a declined submission, they can try again
            if ($userSubmission->status === JobSubmission::STATUS_DECLINED) {
                $userSubmission->update([
                    'status' => JobSubmission::STATUS_CLAIMED,
                    'message' => null
                ]);
                
                // Delete any existing files
                foreach ($userSubmission->files as $file) {
                    Storage::disk('public')->delete($file->file_path);
                    $file->delete();
                }
                
                return redirect('/jobs/' . $job->id)->with('success', 'You have claimed this help request. Please complete your application.');
            }
            
            return back()->withErrors([
                'error' => 'You have already claimed or applied to this help request.'
            ]);
        }

        try {
            // Create the job submission with CLAIMED status
            JobSubmission::create([
                'job_listing_id' => $job->id,
                'user_id' => auth()->id(),
                'message' => null, // Will be filled in later
                'status' => JobSubmission::STATUS_CLAIMED,
                'admin_approved' => false
            ]);

            return redirect('/jobs/' . $job->id)->with('success', 'You have claimed this help request. Please complete your application.');
        } catch (\Exception $e) {
            Log::error('Job claim failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to claim help request: ' . $e->getMessage()
            ]);
        }
    }

    public function complete()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $attributes = request()->validate([
            'submission_id' => ['required', 'exists:job_submissions,id'],
            'message' => ['required', 'min:10'],
            'files.*' => ['nullable', 'file', 'max:51200'] // 50MB max per file
        ]);

        $submission = JobSubmission::findOrFail($attributes['submission_id']);

        // Check if this submission belongs to the current user
        if ($submission->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if the submission is in CLAIMED status
        if ($submission->status !== JobSubmission::STATUS_CLAIMED) {
            return back()->withErrors([
                'error' => 'This application cannot be completed because it is not in the claimed state.'
            ]);
        }

        try {
            DB::transaction(function () use ($submission, $attributes) {
                // Update the submission with the message and change status to PENDING
                $submission->update([
                    'message' => $attributes['message'],
                    'status' => JobSubmission::STATUS_PENDING
                ]);

                // Handle file uploads if any
                if (request()->hasFile('files')) {
                    foreach (request()->file('files') as $file) {
                        $path = $file->store('submission-files', 'public');
                        
                        SubmissionFile::create([
                            'job_submission_id' => $submission->id,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $path,
                            'mime_type' => $file->getMimeType(),
                            'file_size' => $file->getSize()
                        ]);
                    }
                }
            });

            return redirect('/submissions')->with('success', 'Your application has been completed and submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Job submission completion failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to complete application: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function index()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Get submissions for jobs created by the current user
        $receivedSubmissions = JobSubmission::whereHas('jobListing', function ($query) {
            $query->where('user_id', auth()->id());
        })->with(['jobListing', 'user'])->latest()->get();

        // Get submissions made by the current user
        $sentSubmissions = JobSubmission::where('user_id', auth()->id())
            ->with('jobListing.user')
            ->latest()
            ->get();

        return view('submissions.index', [
            'receivedSubmissions' => $receivedSubmissions,
            'sentSubmissions' => $sentSubmissions
        ]);
    }

    public function show(JobSubmission $submission)
    {
        // Check if user is authorized to view this submission
        if (auth()->id() !== $submission->user_id && 
            auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('submissions.show', [
            'submission' => $submission->load(['jobListing', 'user', 'files'])
        ]);
    }

    public function approve(JobSubmission $submission)
    {
        // Check if user is the job owner
        if (auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Only pending submissions can be approved
        if ($submission->status !== JobSubmission::STATUS_PENDING) {
            return back()->withErrors([
                'error' => 'Only pending applications can be approved.'
            ]);
        }

        try {
            DB::transaction(function () use ($submission) {
                // Update submission status
                $submission->update([
                    'status' => JobSubmission::STATUS_APPROVED
                ]);

                // Get the job and the helper user
                $job = $submission->jobListing;
                $helper = $submission->user;

                // Transfer credits to the helper
                $helper->update([
                    'time_credits' => $helper->time_credits + $job->time_credits
                ]);

                // Create a transaction record for the helper
                DB::table('transactions')->insert([
                    'user_id' => $helper->id,
                    'amount' => $job->time_credits,
                    'description' => "Earned credits for helping with: {$job->title}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Mark other submissions as declined
                JobSubmission::where('job_listing_id', $job->id)
                    ->where('id', '!=', $submission->id)
                    ->update(['status' => JobSubmission::STATUS_DECLINED]);
                    
                // Delete the job since it's been completed
                $job->delete();
            });

            return redirect('/submissions')->with('success', 'Application approved and credits transferred! The help request has been completed.');
        } catch (\Exception $e) {
            Log::error('Job submission approval failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to approve application: ' . $e->getMessage()
            ]);
        }
    }

    public function decline(JobSubmission $submission)
    {
        // Check if user is the job owner
        if (auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Only pending submissions can be declined
        if ($submission->status !== JobSubmission::STATUS_PENDING) {
            return back()->withErrors([
                'error' => 'Only pending applications can be declined.'
            ]);
        }

        try {
            // Update submission status to admin_review
            $submission->update([
                'status' => JobSubmission::STATUS_ADMIN_REVIEW,
                'admin_notes' => request('admin_notes') ?? 'Declined by job owner, pending admin review.'
            ]);
            
            return redirect('/submissions')->with('success', 'Application has been declined and sent for admin review. An administrator will decide if the credits should be returned to you or awarded to the applicant.');
        } catch (\Exception $e) {
            Log::error('Job submission decline failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to decline application: ' . $e->getMessage()
            ]);
        }
    }

    public function cancel(JobSubmission $submission)
    {
        // Check if user is the applicant
        if (auth()->id() !== $submission->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Only claimed submissions can be canceled
        if ($submission->status !== JobSubmission::STATUS_CLAIMED) {
            return back()->withErrors([
                'error' => 'Only claimed applications can be canceled.'
            ]);
        }

        try {
            // Store job ID before deleting submission
            $jobId = $submission->job_listing_id;
            
            // Delete any files associated with this submission
            foreach ($submission->files as $file) {
                Storage::disk('public')->delete($file->file_path);
                $file->delete();
            }
            
            // Delete the submission
            $submission->delete();

            // Redirect to the job page so they can claim it again if desired
            return redirect('/jobs/' . $jobId)->with('success', 'Your claim has been canceled. You can now claim this help request again if you wish.');
        } catch (\Exception $e) {
            Log::error('Job claim cancellation failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to cancel claim: ' . $e->getMessage()
            ]);
        }
    }
}
