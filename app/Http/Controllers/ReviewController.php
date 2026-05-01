<?php

// Šis fails saglabā atsauksmes par apstiprinātiem darba pieteikumiem.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobSubmission;
use App\Models\Review;

class ReviewController extends Controller
{
    // Saglabā jaunu atsauksmi, ja to pievieno darba īpašnieks un pieteikums ir apstiprināts.
    public function store(Request $request, JobSubmission $submission)
    {
        $user = $request->user();

        // Atsauksmi drīkst atstāt tikai tas lietotājs, kurš izveidoja darbu.
        if ($user->id !== $submission->jobListing->user_id) {
            abort(403);
        }

        // Atsauksme ir pieejama tikai pēc darba apstiprināšanas.
        if ($submission->status !== JobSubmission::STATUS_APPROVED) {
            return back()->withErrors(['submission' => 'Atsauksmi var atstāt tikai pēc tam, kad darbs ir apstiprināts.']);
        }

        // Vienam pieteikumam tiek ļauta tikai viena atsauksme.
        if ($submission->review) {
            return back()->withErrors(['review' => 'Šim iesniegumam atsauksme jau ir atstāta.']);
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review = Review::create([
            'reviewer_id' => $user->id,
            'reviewee_id' => $submission->user_id,
            'job_submission_id' => $submission->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Atsauksme iesniegta veiksmīgi.');
    }
}
