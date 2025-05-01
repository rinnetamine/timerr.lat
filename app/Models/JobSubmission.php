<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobSubmission extends Model {
    use HasFactory;

    protected $table = 'job_submissions';

    protected $fillable = [
        'job_listing_id',
        'user_id',
        'message',
        'status',
        'admin_notes',
        'admin_approved'
    ];

    // Status constants
    const STATUS_CLAIMED = 'claimed';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DECLINED = 'declined';
    const STATUS_ADMIN_REVIEW = 'admin_review';

    public function jobListing()
    {
        return $this->belongsTo(Job::class, 'job_listing_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(SubmissionFile::class);
    }
}
