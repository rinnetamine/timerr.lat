<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\JobSubmission;

class SubmissionFile extends Model
{
    use HasFactory;

    protected $table = 'submission_files';

    protected $fillable = [
        'job_submission_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size'
    ];

    // belongs to a job submission
    public function jobSubmission()
    {
        return $this->belongsTo(JobSubmission::class);
    }
}
