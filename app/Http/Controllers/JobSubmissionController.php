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
    // download a submission file securely
    public function downloadFile(SubmissionFile $file)
    {
        // check if user is authorized to download this file
        $submission = $file->jobSubmission;
        
        // only allow download for job owner or the applicant who uploaded it
        if (auth()->id() !== $submission->jobListing->user_id && auth()->id() !== $submission->user_id) {
            abort(403, 'You are not authorized to download this file.');
        }
        
        // verify file exists in storage
        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'File not found.');
        }
        
        // get the file from storage and prepare for download
        $path = Storage::disk('public')->path($file->file_path);
        $content = file_get_contents($path);
        $filename = $file->file_name;
        
        // send file as download response with proper headers
        return Response::make($content, 200, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
    
    // claim a job to indicate interest in completing it 
    public function claim()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $attributes = request()->validate([
            'job_id' => ['required', 'exists:job_listings,id'],
        ]);

        $job = Job::findOrFail($attributes['job_id']);

        // prevent users from claiming their own jobs
        if (auth()->id() === $job->user_id) {
            return back()->withErrors([
                'error' => 'You cannot apply to your own help request.'
            ]);
        }

        // check if job is already claimed or in progress
        $existingClaim = JobSubmission::where('job_listing_id', $job->id)
            ->whereIn('status', [JobSubmission::STATUS_CLAIMED, JobSubmission::STATUS_PENDING, JobSubmission::STATUS_APPROVED])
            ->exists();

        if ($existingClaim) {
            return back()->withErrors([
                'error' => 'This help request has already been claimed by another user.'
            ]);
        }

        // check if user already has a submission for this job
        $userSubmission = JobSubmission::where('job_listing_id', $job->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($userSubmission) {
            // allow re-claiming if previous submission was declined
            if ($userSubmission->status === JobSubmission::STATUS_DECLINED) {
                $userSubmission->update([
                    'status' => JobSubmission::STATUS_CLAIMED,
                    'message' => null
                ]);
                
                // clean up any previous files
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
            // create new submission with claimed status
            JobSubmission::create([
                'job_listing_id' => $job->id,
                'user_id' => auth()->id(),
                'message' => null, // will be filled in during complete step
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

    // complete a claimed job by submitting work details and files
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

        // verify submission belongs to current user
        if ($submission->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // only claimed submissions can be completed
        if ($submission->status !== JobSubmission::STATUS_CLAIMED) {
            return back()->withErrors([
                'error' => 'This application cannot be completed because it is not in the claimed state.'
            ]);
        }

        try {
            DB::transaction(function () use ($submission, $attributes) {
                // update submission with message and change status to pending review
                $submission->update([
                    'message' => $attributes['message'],
                    'status' => JobSubmission::STATUS_PENDING
                ]);

                // handle file uploads if any were provided
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

    // display a list of all submissions for the current user
    public function index()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        // get submissions for jobs the user created (received applications)
        $receivedSubmissions = JobSubmission::whereHas('jobListing', function ($query) {
            $query->where('user_id', auth()->id());
        })->with(['jobListing', 'user'])->latest()->get();

        // get submissions the user made to other jobs (sent applications)
        $sentSubmissions = JobSubmission::where('user_id', auth()->id())
            ->with('jobListing.user')
            ->latest()
            ->get();

        return view('submissions.index', [
            'receivedSubmissions' => $receivedSubmissions,
            'sentSubmissions' => $sentSubmissions
        ]);
    }

    // show details of a specific submission
    public function show(JobSubmission $submission)
    {
        // only allow job owner or applicant to view submission details
        if (auth()->id() !== $submission->user_id && 
            auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('submissions.show', [
            'submission' => $submission->load(['jobListing', 'user', 'files'])
        ]);
    }

    // approve a submission and transfer credits to the helper
    public function approve(JobSubmission $submission)
    {
        // verify user is the job owner
        if (auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // only pending submissions can be approved
        if ($submission->status !== JobSubmission::STATUS_PENDING) {
            return back()->withErrors([
                'error' => 'Only pending applications can be approved.'
            ]);
        }

        try {
            DB::transaction(function () use ($submission) {
                // mark submission as approved
                $submission->update([
                    'status' => JobSubmission::STATUS_APPROVED
                ]);

                // get job and helper user
                $job = $submission->jobListing;
                $helper = $submission->user;

                // transfer credits to the helper
                $helper->update([
                    'time_credits' => $helper->time_credits + $job->time_credits
                ]);

                // record the transaction for audit purposes
                DB::table('transactions')->insert([
                    'user_id' => $helper->id,
                    'amount' => $job->time_credits,
                    'description' => "Earned credits for helping with: {$job->title}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // mark all other submissions for this job as declined
                JobSubmission::where('job_listing_id', $job->id)
                    ->where('id', '!=', $submission->id)
                    ->update(['status' => JobSubmission::STATUS_DECLINED]);
                    
                // delete the job since it's been completed
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

    // decline a submission and send it for admin review
    public function decline(JobSubmission $submission)
    {
        // verify user is the job owner
        if (auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // only pending submissions can be declined
        if ($submission->status !== JobSubmission::STATUS_PENDING) {
            return back()->withErrors([
                'error' => 'Only pending applications can be declined.'
            ]);
        }

        try {
            // send to admin review instead of direct decline
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

    // cancel a claimed job submission
    public function cancel(JobSubmission $submission)
    {
        // verify user is the applicant
        if (auth()->id() !== $submission->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // only claimed submissions can be canceled
        if ($submission->status !== JobSubmission::STATUS_CLAIMED) {
            return back()->withErrors([
                'error' => 'Only claimed applications can be canceled.'
            ]);
        }

        try {
            // save job id before deleting the submission
            $jobId = $submission->job_listing_id;
            
            // clean up any files associated with this submission
            foreach ($submission->files as $file) {
                Storage::disk('public')->delete($file->file_path);
                $file->delete();
            }
            
            // delete the submission entirely
            $submission->delete();

            // redirect to job page so user can claim again if desired
            return redirect('/jobs/' . $jobId)->with('success', 'Your claim has been canceled. You can now claim this help request again if you wish.');
        } catch (\Exception $e) {
            Log::error('Job claim cancellation failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to cancel claim: ' . $e->getMessage()
            ]);
        }
    }
}
