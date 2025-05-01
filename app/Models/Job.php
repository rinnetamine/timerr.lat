<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    protected $table = 'job_listings';

    protected $fillable = [
        'title',
        'description',
        'time_credits',
        'category',
        'user_id'
    ];

    // belongs to the user who created the job listing
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // has many submissions from users applying to help
    public function submissions()
    {
        return $this->hasMany(JobSubmission::class, 'job_listing_id');
    }
}
