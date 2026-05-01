<?php

// Šis fails pārvalda darba pieteikuma dzīves ciklu no darba saņemšanas līdz apstiprināšanai vai noraidīšanai.

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

class JobSubmissionController extends Controller
{
    // Pārbauda piekļuves tiesības un izsniedz pieteikumam pievienoto failu.
    public function downloadFile(SubmissionFile $file)
    {
        $submission = $file->jobSubmission;
        
        // Failu drīkst lejupielādēt darba īpašnieks, pieteicējs vai administrators.
        if (auth()->id() !== $submission->jobListing->user_id && 
            auth()->id() !== $submission->user_id && 
            !auth()->user()->isAdmin()) {
            abort(403, 'Jums nav atļauts lejupielādēt šo failu.');
        }
        
        // Pirms izsniegšanas tiek pārbaudīts, vai fails eksistē publiskajā krātuvē.
        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'Fails nav atrasts.');
        }
        
        $path = Storage::disk('public')->path($file->file_path);
        $content = file_get_contents($path);
        $filename = $file->file_name;
        
        // Fails tiek atgriezts ar lejupielādei nepieciešamām galvenēm.
        return Response::make($content, 200, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
    
    // Ļauj lietotājam saņemt darbu, ja tas vēl nav aizņemts vai iesaldēts.
    public function claim()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Pirms saglabāšanas tiek validēti lietotāja ievaddati.
        $attributes = request()->validate([
            'job_id' => ['required', 'exists:job_listings,id'],
        ]);

        $job = Job::findOrFail($attributes['job_id']);

        // Lietotājs nevar pieteikties pats savam palīdzības pieprasījumam.
        if (auth()->id() === $job->user_id) {
            return back()->withErrors([
                'error' => 'Jūs nevarat pieteikties savam pašam palīdzības pieprasījumam.'
            ]);
        }

        // Pirms darba saņemšanas tiek pārbaudīts, vai darbs jau nav aizņemts.
        $existingClaim = JobSubmission::where('job_listing_id', $job->id)
            ->whereIn('status', [JobSubmission::STATUS_CLAIMED, JobSubmission::STATUS_PENDING, JobSubmission::STATUS_APPROVED])
            ->exists();

        if ($existingClaim) {
            return back()->withErrors([
                'error' => 'Šo palīdzības pieprasījumu jau ir saņēmis cits lietotājs.'
            ]);
        }

        // Darbu nevar saņemt, ja tam jau ir aktīvs strīds.
        $disputedSubmission = JobSubmission::where('job_listing_id', $job->id)
            ->where('dispute_status', '!=', JobSubmission::DISPUTE_NONE)
            ->where('dispute_status', '!=', JobSubmission::DISPUTE_RESOLVED)
            ->first();

        if ($disputedSubmission) {
            return back()->withErrors([
                'error' => 'Šis palīdzības pieprasījums ir aizsaldēts strīda dēļ un nav pieejams.'
            ]);
        }

        // Tiek pārbaudīts, vai pašreizējam lietotājam jau ir pieteikums šim darbam.
        $userSubmission = JobSubmission::where('job_listing_id', $job->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($userSubmission) {
            // Noraidītu pieteikumu lietotājs var saņemt atkārtoti.
            if ($userSubmission->status === JobSubmission::STATUS_DECLINED) {
                $userSubmission->update([
                    'status' => JobSubmission::STATUS_CLAIMED,
                    'message' => null
                ]);
                
                foreach ($userSubmission->files as $file) {
                    // Atkārtoti saņemot darbu, iepriekšējie faili tiek sakopti.
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
            // Tiek izveidots jauns pieteikums ar saņemta darba statusu.
            JobSubmission::create([
                'job_listing_id' => $job->id,
                'user_id' => auth()->id(),
                'message' => null,
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

    // Saglabā izpildītā darba aprakstu un pievienotos failus.
    public function complete()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Žurnālā tiek pierakstīta informācija par augšupielādētajiem failiem.
        Log::info('Request files: ' . json_encode(request()->allFiles()));
        Log::info('Request has files: ' . (request()->hasFile('files') ? 'true' : 'false'));
        
        // Pirms saglabāšanas tiek validēti lietotāja ievaddati.
        $attributes = request()->validate([
            'submission_id' => ['required', 'exists:job_submissions,id'],
            'message' => ['required', 'string', 'min:10', 'max:1000']
        ]);

        $submission = JobSubmission::findOrFail($attributes['submission_id']);

        // Lietotājs drīkst pabeigt tikai savu pieteikumu.
        if ($submission->user_id !== auth()->id()) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }

        // Pabeigt drīkst tikai saņemtus pieteikumus.
        if ($submission->status !== JobSubmission::STATUS_CLAIMED) {
            return back()->withErrors([
                'error' => 'Šo pieteikumu nevar pabeigt, jo tas nav saņemts.'
            ]);
        }

        // Pieteikumu nevar pabeigt, ja tas pašlaik ir iesaldēts strīda dēļ.
        if ($submission->dispute_status !== JobSubmission::DISPUTE_NONE && $submission->dispute_status !== JobSubmission::DISPUTE_RESOLVED) {
            return back()->withErrors([
                'error' => 'Šis pieteikums ir aizsaldēts strīda dēļ un to nevar pabeigt.'
            ]);
        }

        // Tiek pārbaudīts, vai šim darbam nav citu aktīvu strīdu.
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
            // Saistītās datubāzes izmaiņas tiek izpildītas vienā transakcijā.
            DB::transaction(function () use ($submission, $attributes) {
                // Pieteikums tiek papildināts ar darba aprakstu un nodots pārskatīšanai.
                $submission->update([
                    'message' => $attributes['message'],
                    'status' => JobSubmission::STATUS_PENDING
                ]);

                // Ja pieteikumam ir pielikumi, tie tiek saglabāti atsevišķi publiskajā krātuvē.
                if (request()->hasFile('files')) {
                    foreach (request()->file('files') as $index => $file) {
                        // Nederīgi faili tiek izlaisti un pierakstīti žurnālā.
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

    // Sadala lietotāja pieteikumus nosūtītajos un saņemtajos pieteikumos.
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

        // Tiek atlasīti pieteikumi lietotāja izveidotajiem darbiem.
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

        // Tiek atlasīti pieteikumi, ko lietotājs nosūtījis citu autoru darbiem.
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

    // Piemēro statusa un meklēšanas filtrus pieteikumu sarakstam.
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

    // Sagatavo viena pieteikuma detalizētu skatu.
    public function show(JobSubmission $submission)
    {
        // Pieteikumu drīkst skatīt tikai darba autors vai pieteikuma iesniedzējs.
        if (auth()->id() !== $submission->user_id && 
            auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }

        return view('submissions.show', [
            'submission' => $submission->load(['jobListing', 'user', 'files'])
        ]);
    }

    // Sagatavo pieteikuma informāciju PDF eksportam.
    public function exportHtml(JobSubmission $submission)
    {
        // PDF eksportu drīkst veikt tikai darba autors vai pieteikuma iesniedzējs.
        if (auth()->id() !== $submission->user_id && auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }

        $submission->load(['jobListing', 'user', 'files']);

        // Tiek sagatavoti PDF ģeneratora iestatījumi.
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        
        // Tiek izveidota DomPDF instance.
        $dompdf = new Dompdf($options);
        
        // HTML skats tiek pārvērsts PDF dokumentā.
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

    // Apstiprina pieteikumu un pārskaita nopelnītos kredītus darba veicējam.
    public function approve(JobSubmission $submission)
    {
        // Apstiprināt drīkst tikai darba īpašnieks.
        if (auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }

        // Apstiprināt drīkst tikai gaidošus pieteikumus.
        if ($submission->status !== JobSubmission::STATUS_PENDING) {
            return back()->withErrors([
                'error' => 'Apstiprināt var tikai gaidošus pieteikumus.'
            ]);
        }

        try {
            // Saistītās datubāzes izmaiņas tiek izpildītas vienā transakcijā.
            DB::transaction(function () use ($submission) {
                // Pieteikums tiek atzīmēts kā apstiprināts.
                $submission->update([
                    'status' => JobSubmission::STATUS_APPROVED
                ]);

                // Tiek iegūts darbs un darba veicējs.
                $job = $submission->jobListing;
                $helper = $submission->user;

                // Pēc apstiprināšanas kredīti tiek pieskaitīti darba veicējam.
                $helper->update([
                    'time_credits' => $helper->time_credits + $job->time_credits
                ]);

                // Kredītu kustība tiek pierakstīta darījumu vēsturē.
                DB::table('transactions')->insert([
                    'user_id' => $helper->id,
                    'amount' => $job->time_credits,
                    'description' => "Nopelnīti kredīti par palīdzību darbā: {$job->title}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Pēc viena pieteikuma apstiprināšanas pārējie pieteikumi tiek noraidīti.
                JobSubmission::where('job_listing_id', $job->id)
                    ->where('id', '!=', $submission->id)
                    ->update(['status' => JobSubmission::STATUS_DECLINED]);
                
            });

            // Pēc apstiprināšanas darba īpašnieks tiek novirzīts uz atsauksmes pievienošanu.
            return redirect('/submissions/' . $submission->id)->with('success', 'Pieteikums apstiprināts, un kredīti ir pārskaitīti. Zemāk varat atstāt atsauksmi palīgam.');
        } catch (\Exception $e) {
            Log::error('Job submission approval failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Neizdevās apstiprināt pieteikumu: ' . $e->getMessage()
            ]);
        }
    }

    // Noraida pieteikumu un nodod gadījumu administratora pārskatīšanai.
    public function decline(JobSubmission $submission)
    {
        // Noraidīt drīkst tikai darba īpašnieks.
        if (auth()->id() !== $submission->jobListing->user_id) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }
        
        // Noraidīt drīkst tikai gaidošus pieteikumus.
        if ($submission->status !== JobSubmission::STATUS_PENDING) {
            return back()->withErrors([
                'error' => 'Noraidīt var tikai gaidošus pieteikumus.'
            ]);
        }

        try {
            // Noraidījums tiek pārvērsts par strīdu administratora pārskatīšanai.
            $submission->update([
                'status' => JobSubmission::STATUS_ADMIN_REVIEW,
                'admin_notes' => request('admin_notes') ?? 'Darba īpašnieks noraidīja pieteikumu. Nepieciešama administratora pārskatīšana.',
                'dispute_status' => JobSubmission::DISPUTE_REQUESTED,
                'dispute_reason' => request('admin_notes') ?? 'Darba īpašnieks noraidīja šo iesniegumu. Iemesls: ' . (request('admin_notes') ?? 'Iemesls nav norādīts'),
                'dispute_initiated_by' => auth()->id(),
                // Iesaldēšana aptur turpmākas darbības līdz lēmuma pieņemšanai.
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

    // Ļauj darba veicējam atcelt vēl nepabeigtu pieteikumu.
    public function cancel(JobSubmission $submission)
    {
        // Atcelt drīkst tikai pieteikuma iesniedzējs.
        if (auth()->id() !== $submission->user_id) {
            abort(403, 'Nepietiekamas piekļuves tiesības.');
        }

        // Atcelt drīkst tikai saņemtu, vēl nepabeigtu pieteikumu.
        if ($submission->status !== JobSubmission::STATUS_CLAIMED) {
            return back()->withErrors([
                'error' => 'Tikai saņemtos pieteikumus var atcelt.'
            ]);
        }

        try {
            // Darba ID tiek saglabāts pirms pieteikuma dzēšanas.
            $jobId = $submission->job_listing_id;
            
            foreach ($submission->files as $file) {
                // Dzēšot pieteikumu, tiek sakopts arī saistītais fails.
                Storage::disk('public')->delete($file->file_path);
                $file->delete();
            }
            
            // Pieteikums tiek pilnībā dzēsts.
            $submission->delete();

            // Lietotājs tiek atgriezts darba lapā, lai pēc vajadzības varētu to saņemt vēlreiz.
            return redirect('/jobs/' . $jobId)->with('success', 'Jūsu saņemšana ir atcelta. Jūs tagad varat atkal saņemt šo palīdzības pieprasījumu, ja vēlaties.');
        } catch (\Exception $e) {
            Log::error('Job claim cancellation failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Neizdevās atcelt saņemšanu: ' . $e->getMessage()
            ]);
        }
    }
}
