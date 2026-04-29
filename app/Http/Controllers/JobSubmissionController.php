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
use Dompdf\Dompdf;
use Dompdf\Options;

// controller for managing job submissions
class JobSubmissionController extends Controller
{
    // handle secure file downloads for submissions
    public function downloadFile(SubmissionFile $file)
    {
        // verify user has permission to download
        $submission = $file->jobSubmission;
        
        // allow job owner, applicant, or admin to download
        if (auth()->id() !== $submission->jobListing->user_id && 
            auth()->id() !== $submission->user_id && 
            !auth()->user()->isAdmin()) {
            abort(403, 'Jums nav atļauts lejupielādēt šo failu.');
        }
        
        // verify file exists
        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'Fails nav atrasts.');
        }
        
        // prepare file for download
        $path = Storage::disk('public')->path($file->file_path);
        $content = file_get_contents($path);
        $filename = $file->file_name;
        
        // send file with proper headers
        return Response::make($content, 200, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
    
    // handle job claiming process
    public function claim()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        // validate job ID
        $attributes = request()->validate([
            'job_id' => ['required', 'exists:job_listings,id'],
        ]);

        $job = Job::findOrFail($attributes['job_id']);

        // prevent self-claiming
        if (auth()->id() === $job->user_id) {
            return back()->withErrors([
                'error' => 'Jūs nevarat pieteikties savam pašam palīdzības pieprasījumam.'
            ]);
        }

        // check for existing claims
        $existingClaim = JobSubmission::where('job_listing_id', $job->id)
            ->whereIn('status', [JobSubmission::STATUS_CLAIMED, JobSubmission::STATUS_PENDING, JobSubmission::STATUS_APPROVED])
            ->exists();

        if ($existingClaim) {
            return back()->withErrors([
                'error' => 'Šo palīdzības pieprasījumu jau ir saņēmis cits lietotājs.'
            ]);
        }

        // check for disputed submissions - prevent claiming if any dispute exists
        $disputedSubmission = JobSubmission::where('job_listing_id', $job->id)
            ->where('dispute_status', '!=', JobSubmission::DISPUTE_NONE)
            ->where('dispute_status', '!=', JobSubmission::DISPUTE_RESOLVED)
            ->first();

        if ($disputedSubmission) {
            return back()->withErrors([
                'error' => 'Šis palīdzības pieprasījums ir aizsaldēts strīda dēļ un nav pieejams.'
            ]);
        }

        // check for existing user submission
        $userSubmission = JobSubmission::where('job_listing_id', $job->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($userSubmission) {
            // handle re-claiming for declined submissions
            if ($userSubmission->status === JobSubmission::STATUS_DECLINED) {
                $userSubmission->update([
                    'status' => JobSubmission::STATUS_CLAIMED,
                    'message' => null
                ]);
                
                // clean up previous files
                foreach ($userSubmission->files as $file) {
                    Storage::disk('public')->delete($file->file_path);
                    $file->delete();
                }
                
                return redirect('/submissions')->with('success', 'Jūs esat saņēmis šo palīdzības pieprasījumu. Lūdzu, pabeidziet savu pieteikumu.');
            }
            
            return back()->withErrors([
                'error' => 'Jūs jau esat saņēmis vai pieteicies šim palīdzības pieprasījumam.'
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

                return redirect('/submissions')->with('success', 'Jūs esat saņēmis šo palīdzības pieprasījumu. Lūdzu, pabeidziet savu pieteikumu.');
        } catch (\Exception $e) {
            Log::error('Job claim failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Neizdevās saņemt palīdzības pieprasījumu: ' . $e->getMessage()
            ]);
        }
    }

    // complete a claimed job by submitting work details and files
    public function complete()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Debug: Check what files are in the request
        Log::info('Request files: ' . json_encode(request()->allFiles()));
        Log::info('Request has files: ' . (request()->hasFile('files') ? 'true' : 'false'));
        
        $attributes = request()->validate([
            'submission_id' => ['required', 'exists:job_submissions,id'],
            'message' => ['required', 'string', 'min:10', 'max:1000']
            // Files removed temporarily for testing
        ]);

        $submission = JobSubmission::findOrFail($attributes['submission_id']);

        // verify submission belongs to current user
        if ($submission->user_id !== auth()->id()) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }

        // only claimed submissions can be completed
        if ($submission->status !== JobSubmission::STATUS_CLAIMED) {
            return back()->withErrors([
                'error' => 'Šo pieteikumu nevar pabeigt, jo tas nav saņemts.'
            ]);
        }

        // check if submission is disputed/frozen
        if ($submission->dispute_status !== JobSubmission::DISPUTE_NONE && $submission->dispute_status !== JobSubmission::DISPUTE_RESOLVED) {
            return back()->withErrors([
                'error' => 'Šis pieteikums ir aizsaldēts strīda dēļ un to nevar pabeigt.'
            ]);
        }

        // check if the job itself has any disputed submissions
        $jobDisputedSubmission = JobSubmission::where('job_listing_id', $submission->job_listing_id)
            ->where('dispute_status', '!=', JobSubmission::DISPUTE_NONE)
            ->where('dispute_status', '!=', JobSubmission::DISPUTE_RESOLVED)
            ->first();

        if ($jobDisputedSubmission) {
            return back()->withErrors([
                'error' => 'Darbs ir aizsaldzis strīdu dēļ un nevar pabeigt pieteikumu.'
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
                    foreach (request()->file('files') as $index => $file) {
                        // Debug: Check if file is valid
                        if (!$file->isValid()) {
                            Log::error("File {$index} is not valid: " . $file->getErrorMessage());
                            continue;
                        }
                        
                        try {
                            $path = $file->store('submission-files', 'public');
                            
                            SubmissionFile::create([
                                'job_submission_id' => $submission->id,
                                'file_name' => $file->getClientOriginalName(),
                                'file_path' => $path,
                                'mime_type' => $file->getMimeType(),
                                'file_size' => $file->getSize()
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Failed to store file {$index}: " . $e->getMessage());
                            throw $e;
                        }
                    }
                }
            });

            return redirect('/submissions')->with('success', 'Jūsu pieteikums ir pabeigts un veiksmīgi iesniegts.');
        } catch (\Exception $e) {
            Log::error('Job submission completion failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Neizdevās pabeigt pieteikumu: ' . $e->getMessage()
            ])->withInput();
        }
    }

    // display a list of all submissions for the current user
    public function index()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $statuses = [
            JobSubmission::STATUS_CLAIMED,
            JobSubmission::STATUS_PENDING,
            JobSubmission::STATUS_APPROVED,
            JobSubmission::STATUS_DECLINED,
            JobSubmission::STATUS_ADMIN_REVIEW,
        ];

        // get submissions for jobs the user created (received applications)
        $receivedSubmissionsQuery = JobSubmission::whereHas('jobListing', function ($query) {
            $query->where('user_id', auth()->id());
        })->with(['jobListing', 'user']);

        $this->applyIndexFilters(
            $receivedSubmissionsQuery,
            request('received_status'),
            request('received_search'),
            $statuses
        );

        $receivedSubmissions = $receivedSubmissionsQuery
            ->latest()
            ->paginate(8, ['*'], 'received_page')
            ->withQueryString();

        // get submissions the user made to other jobs (sent applications)
        $sentSubmissionsQuery = JobSubmission::where('user_id', auth()->id())
            ->with('jobListing.user');

        $this->applyIndexFilters(
            $sentSubmissionsQuery,
            request('sent_status'),
            request('sent_search'),
            $statuses
        );

        $pendingSubmissionsCount = (clone $receivedSubmissionsQuery)->where('status', JobSubmission::STATUS_PENDING)->count()
            + (clone $sentSubmissionsQuery)->where('status', JobSubmission::STATUS_PENDING)->count();

        $sentSubmissions = $sentSubmissionsQuery
            ->latest()
            ->paginate(8, ['*'], 'sent_page')
            ->withQueryString();

        return view('submissions.index', [
            'receivedSubmissions' => $receivedSubmissions,
            'sentSubmissions' => $sentSubmissions,
            'submissionStatuses' => $statuses,
            'pendingSubmissionsCount' => $pendingSubmissionsCount,
        ]);
    }

    private function applyIndexFilters($query, ?string $status, ?string $search, array $allowedStatuses): void
    {
        $search = trim((string) $search);

        if (in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('message', 'like', "%{$search}%")
                    ->orWhereHas('jobListing', function ($query) use ($search) {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('jobListing.user', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }
    }

    // show details of a specific submission
    public function show(JobSubmission $submission)
    {
        // only allow job owner or applicant to view submission details
        if (auth()->id() !== $submission->user_id && 
            auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }

        return view('submissions.show', [
            'submission' => $submission->load(['jobListing', 'user', 'files'])
        ]);
    }

    // export submission details as PDF
    public function exportHtml(JobSubmission $submission)
    {
        // only allow job owner or applicant to export
        if (auth()->id() !== $submission->user_id && auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }

        $submission->load(['jobListing', 'user', 'files']);

        // configure domPDF options
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        
        // create new DomPDF instance
        $dompdf = new Dompdf($options);
        
        // load HTML content
        $html = view('submissions.pdf', ['submission' => $submission])->render();
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = 'iesniegums-' . $submission->id . '.pdf';
        
        return Response::make($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    // approve a submission and transfer credits to the helper
    public function approve(JobSubmission $submission)
    {
        // verify user is the job owner
        if (auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }

        // only pending submissions can be approved
        if ($submission->status !== JobSubmission::STATUS_PENDING) {
            return back()->withErrors([
                'error' => 'Apstiprināt var tikai gaidošus pieteikumus.'
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
                    'description' => "Nopelnīti kredīti par palīdzību darbā: {$job->title}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // mark all other submissions for this job as declined
                JobSubmission::where('job_listing_id', $job->id)
                    ->where('id', '!=', $submission->id)
                    ->update(['status' => JobSubmission::STATUS_DECLINED]);
                
            });

            // redirect to the submission detail so the job owner can leave a review
            return redirect('/submissions/' . $submission->id)->with('success', 'Pieteikums apstiprināts, un kredīti ir pārskaitīti. Zemāk varat atstāt atsauksmi palīgam.');
        } catch (\Exception $e) {
            Log::error('Job submission approval failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Neizdevās apstiprināt pieteikumu: ' . $e->getMessage()
            ]);
        }
    }

    // decline a submission and send it for admin review
    public function decline(JobSubmission $submission)
    {
        // verify user is the job owner
        if (auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }
        
        // only pending submissions can be declined
        if ($submission->status !== JobSubmission::STATUS_PENDING) {
            return back()->withErrors([
                'error' => 'Noraidīt var tikai gaidošus pieteikumus.'
            ]);
        }

        try {
            // send to admin review instead of direct decline - create automatic dispute
            $submission->update([
                'status' => JobSubmission::STATUS_ADMIN_REVIEW,
                'admin_notes' => request('admin_notes') ?? 'Darba īpašnieks noraidīja pieteikumu. Nepieciešama administratora pārskatīšana.',
                'dispute_status' => JobSubmission::DISPUTE_REQUESTED,
                'dispute_reason' => request('admin_notes') ?? 'Darba īpašnieks noraidīja šo iesniegumu. Iemesls: ' . (request('admin_notes') ?? 'Iemesls nav norādīts'),
                'dispute_initiated_by' => auth()->id(),
                'is_frozen' => true,
                'freeze_reason' => 'Automātiski iesaldēts darba īpašnieka noraidījuma dēļ'
            ]);
            
            return redirect('/submissions')->with('success', 'Pieteikums ir noraidīts, un automātiski izveidots strīds. Administrators pārskatīs šo gadījumu.');
        } catch (\Exception $e) {
            Log::error('Job submission decline failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Neizdevās noraidīt pieteikumu: ' . $e->getMessage()
            ]);
        }
    }

    // cancel a claimed job submission
    public function cancel(JobSubmission $submission)
    {
        // verify user is the applicant
        if (auth()->id() !== $submission->user_id) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }

        // only claimed submissions can be canceled
        if ($submission->status !== JobSubmission::STATUS_CLAIMED) {
            return back()->withErrors([
                'error' => 'Tikai saņemtos pieteikumus var atcelt.'
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
            return redirect('/jobs/' . $jobId)->with('success', 'Jūsu saņemšana ir atcelta. Jūs tagad varat atkal saņemt šo palīdzības pieprasījumu, ja vēlaties.');
        } catch (\Exception $e) {
            Log::error('Job claim cancellation failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Neizdevās atcelt saņemšanu: ' . $e->getMessage()
            ]);
        }
    }
}
