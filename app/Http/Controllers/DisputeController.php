<?php

// Šis fails pārvalda strīdu iesniegšanu un administratora lēmumu piemērošanu.
// Strīda laikā pieteikums tiek iesaldēts, lai sistēmas stāvoklis līdz lēmumam nemainītos nekontrolēti.

namespace App\Http\Controllers;

use App\Models\JobSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
    // Sagatavo strīda izveides formu konkrētam pieteikumam.
    public function create(JobSubmission $submission)
    {
        // Strīdu drīkst sākt tikai pieteikumā iesaistīts lietotājs.
        $user = Auth::user();
        if ($submission->user_id !== $user->id && $submission->jobListing->user_id !== $user->id) {
            return back()->with('error', 'Jums nav tiesību strīdēties par šo iesniegumu.');
        }

        return view('disputes.create', [
            'submission' => $submission->load(['jobListing', 'user'])
        ]);
    }

    // Reģistrē strīda iemeslu un iesaldē pieteikumu līdz administratora lēmumam.
    public function store(Request $request, JobSubmission $submission)
    {
        // Pirms saglabāšanas tiek validēts strīda pamatojums.
        $request->validate([
            'reason' => 'required|string|min:10|max:1000'
        ]);

        // Atkārtota piekļuves pārbaude aizsargā tiešo POST pieprasījumu.
        $user = Auth::user();
        if ($submission->user_id !== $user->id && $submission->jobListing->user_id !== $user->id) {
            return back()->with('error', 'Jums nav tiesību strīdēties par šo iesniegumu.');
        }

        // Pieteikums tiek nodots administratora pārskatīšanai kā aktīvs strīds.
        $submission->update([
            'dispute_status' => JobSubmission::DISPUTE_REQUESTED,
            'dispute_reason' => $request->reason,
            'dispute_initiated_by' => $user->id,
            // Iesaldēšana aptur turpmākas darbības līdz lēmuma pieņemšanai.
            'is_frozen' => true,
            'freeze_reason' => 'Strīdu uzsāka ' . $user->first_name . ' ' . $user->last_name
        ]);

        return redirect()->route('submissions.show', $submission)
            ->with('success', 'Strīds iesniegts. Administrators to pārskatīs drīz.');
    }

    // Parāda administratoram aktīvos un atrisinātos strīdus.
    public function index()
    {
        // Administratora skati un darbības nav pieejamas parastiem lietotājiem.
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $disputes = JobSubmission::with(['user', 'jobListing.user', 'disputeInitiator', 'disputeResolver', 'files'])
            ->where(function($query) {
                // Tiek atlasīti gan aktīvie strīdi, gan pieteikumi administratora pārskatīšanā.
                $query->where('dispute_status', '!=', JobSubmission::DISPUTE_NONE)
                      ->orWhere('status', JobSubmission::STATUS_ADMIN_REVIEW);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('disputes.index', compact('disputes'));
    }

    // Parāda administratoram viena strīda detalizētu informāciju.
    public function show(JobSubmission $submission)
    {
        // Administratora skati un darbības nav pieejamas parastiem lietotājiem.
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $submission->load(['user', 'jobListing.user', 'disputeInitiator', 'disputeResolver', 'files']);
        
        return view('disputes.show', compact('submission'));
    }

    // Saglabā administratora lēmumu un piemēro izvēlēto strīda risinājumu.
    public function resolve(Request $request, JobSubmission $submission)
    {
        // Administratora skati un darbības nav pieejamas parastiem lietotājiem.
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'resolution' => 'required|string|min:10|max:1000',
            'action' => 'required|in:approve,decline,unfreeze'
        ]);

        // Pieteikumam tiek saglabāta administratora rezolūcija un atrisināšanas metadati.
        $submission->update([
            'dispute_status' => JobSubmission::DISPUTE_RESOLVED,
            'dispute_resolution' => $request->resolution,
            'dispute_resolved_by' => Auth::id(),
            'dispute_resolved_at' => now()
        ]);

        // Administratora izvēlētā darbība nosaka strīda gala rezultātu.
        switch ($request->action) {
            case 'approve':
                // Apstiprināšanas gadījumā darbs tiek atzīts par izpildītu.
                $submission->update([
                    'status' => JobSubmission::STATUS_APPROVED,
                    'admin_approved' => true,
                    'is_frozen' => false,
                    'freeze_reason' => null
                ]);
                
                // Pēc apstiprināšanas kredīti tiek pārskaitīti darba izpildītājam.
                $submission->user->increment('time_credits', $submission->jobListing->time_credits);
                $submission->jobListing->user->decrement('time_credits', $submission->jobListing->time_credits);
                
                // Katra administratora kredītu korekcija tiek saglabāta darījumu vēsturē.
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
                // Noraidīšanas gadījumā pieteikums tiek atzīmēts kā neveiksmīgs.
                $submission->update([
                    'status' => JobSubmission::STATUS_DECLINED,
                    'admin_approved' => false,
                    'is_frozen' => false,
                    'freeze_reason' => null
                ]);
                break;

            case 'unfreeze':
                // Atiesaldēšana ļauj turpināt darbu bez statusa maiņas.
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
