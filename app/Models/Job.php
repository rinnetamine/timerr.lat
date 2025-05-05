<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    // table name for job listings
    protected $table = 'job_listings';

    // fillable attributes for job listings
    protected $fillable = [
        'title',
        'description',
        'time_credits',
        'category',
        'user_id'
    ];

    // relationship to user who created the job
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // relationship to job submissions
    public function submissions()
    {
        return $this->hasMany(JobSubmission::class, 'job_listing_id');
    }
}
