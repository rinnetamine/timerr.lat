<?php

// Šis fails apraksta pieteikumam pievienotos darba izpildes failus.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\JobSubmission;

class SubmissionFile extends Model
{
    use HasFactory;

    // Modelis izmanto atsevišķu tabulu pieteikumu failiem.
    protected $table = 'submission_files';

    // Šie lauki drīkst tikt aizpildīti pēc faila augšupielādes.
    protected $fillable = [
        'job_submission_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size'
    ];

    // Definē saiti ar darba pieteikumu, kuram fails pievienots.
    public function jobSubmission()
    {
        return $this->belongsTo(JobSubmission::class);
    }
}
