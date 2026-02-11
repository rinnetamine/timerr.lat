<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobSubmission;
use App\Models\Review;

class ReviewController extends Controller
{
    /** store a newly created review.*/
    public function store(Request $request, JobSubmission $submission)
    {
        $user = $request->user();

        // only the job owner can leave a review for this submission
        if ($user->id !== $submission->jobListing->user_id) {
            abort(403);
        }

        // only allow reviews for completed/approved submissions
        if ($submission->status !== JobSubmission::STATUS_APPROVED) {
            return back()->withErrors(['submission' => 'Cannot review until the job is marked completed/approved.']);
        }

        // prevent duplicate reviews
        if ($submission->review) {
            return back()->withErrors(['review' => 'A review has already been left for this submission.']);
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

        return redirect()->back()->with('success', 'Review submitted successfully.');
    }
}
