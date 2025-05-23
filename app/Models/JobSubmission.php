<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'admin_approved'
    ];

    // status constants for tracking submission lifecycle
    const STATUS_CLAIMED = 'claimed';     // initial state when user claims a job
    const STATUS_PENDING = 'pending';     // user has submitted their application
    const STATUS_APPROVED = 'approved';   // job owner approved the submission
    const STATUS_DECLINED = 'declined';   // job owner or admin declined the submission
    const STATUS_ADMIN_REVIEW = 'admin_review'; // submission needs admin decision

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
}
