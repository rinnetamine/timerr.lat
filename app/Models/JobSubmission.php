<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Review;

class JobSubmission extends Model
{
    use HasFactory;

    // table name for job submissions
    protected $table = 'job_submissions';

    // fillable attributes for job submissions
    protected $fillable = [
        'job_listing_id',
        'user_id',
        'message',
        'status',
        'admin_notes',
        'admin_approved',
        'dispute_status',
        'dispute_reason',
        'dispute_initiated_by',
        'dispute_resolution',
        'dispute_resolved_at',
        'dispute_resolved_by',
        'is_frozen',
        'freeze_reason'
    ];

    // cast attributes to proper types
    protected $casts = [
        'admin_approved' => 'boolean',
        'is_frozen' => 'boolean',
        'dispute_resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // status constants for tracking submission lifecycle
    const STATUS_CLAIMED = 'claimed';     // initial state when user claims a job
    const STATUS_PENDING = 'pending';     // user has submitted their application
    const STATUS_APPROVED = 'approved';   // job owner approved the submission
    const STATUS_DECLINED = 'declined';   // job owner or admin declined the submission
    const STATUS_ADMIN_REVIEW = 'admin_review'; // submission needs admin decision

    // dispute status constants
    const DISPUTE_NONE = 'none';
    const DISPUTE_REQUESTED = 'requested';
    const DISPUTE_UNDER_REVIEW = 'under_review';
    const DISPUTE_RESOLVED = 'resolved';

    // relationship to the job listing
    public function jobListing()
    {
        return $this->belongsTo(Job::class, 'job_listing_id');
    }

    // relationship to the user who submitted the application
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // relationship to submission files
    public function files()
    {
        return $this->hasMany(SubmissionFile::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'job_submission_id');
    }

    // relationship to user who initiated the dispute
    public function disputeInitiator()
    {
        return $this->belongsTo(User::class, 'dispute_initiated_by');
    }

    // relationship to admin who resolved the dispute
    public function disputeResolver()
    {
        return $this->belongsTo(User::class, 'dispute_resolved_by');
    }

    // check if submission can be disputed
    public function canBeDisputed()
    {
        // always decline if already frozen with dispute
        if ($this->is_frozen && $this->dispute_status !== self::DISPUTE_NONE) {
            return false;
        }

        // if already has dispute, cannot dispute again
        if ($this->dispute_status !== self::DISPUTE_NONE) {
            return false;
        }

        // always allow disputes for involved parties (no timeout restrictions)
        return true;
    }

    // check if submission is frozen
    public function isFrozen()
    {
        return $this->is_frozen;
    }

    // freeze the submission
    public function freeze($reason = null)
    {
        $this->is_frozen = true;
        $this->freeze_reason = $reason;
        $this->save();
    }

    // unfreeze the submission
    public function unfreeze()
    {
        $this->is_frozen = false;
        $this->freeze_reason = null;
        $this->save();
    }

    // get timeout warning message
    public function getTimeoutWarning()
    {
        if ($this->is_frozen || $this->dispute_status !== self::DISPUTE_NONE) {
            return null;
        }

        $now = now();
        $hoursSinceUpdate = $this->updated_at->diffInHours($now);

        if ($this->status === self::STATUS_CLAIMED && $hoursSinceUpdate > 24) {
            $remainingHours = max(0, 48 - $hoursSinceUpdate);
            return [
                'type' => 'claim_timeout',
                'message' => "Worker has been inactive for " . round($hoursSinceUpdate) . " hours.",
                'hours_remaining' => $remainingHours,
                'severity' => $remainingHours <= 12 ? 'high' : ($remainingHours <= 24 ? 'medium' : 'low')
            ];
        }

        if ($this->status === self::STATUS_PENDING && $hoursSinceUpdate > 48) {
            $remainingHours = max(0, 72 - $hoursSinceUpdate);
            return [
                'type' => 'pending_timeout',
                'message' => "Job has been pending for {$hoursSinceUpdate} hours. Dispute will be available in {$remainingHours} hours if not approved.",
                'hours_remaining' => $remainingHours,
                'severity' => $remainingHours <= 12 ? 'high' : ($remainingHours <= 24 ? 'medium' : 'low')
            ];
        }

        if ($this->status === self::STATUS_APPROVED && $hoursSinceUpdate > 12 && !$this->review) {
            $remainingHours = max(0, 24 - $hoursSinceUpdate);
            return [
                'type' => 'review_timeout',
                'message' => "Job was approved {$hoursSinceUpdate} hours ago but no review left. Dispute will be available in {$remainingHours} hours.",
                'hours_remaining' => $remainingHours,
                'severity' => $remainingHours <= 6 ? 'high' : ($remainingHours <= 12 ? 'medium' : 'low')
            ];
        }

        return null;
    }
}
