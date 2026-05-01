<?php

// Šis fails apraksta lietotāju atsauksmes un to saites ar pieteikumiem.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    // Šie lauki drīkst tikt aizpildīti, veidojot jaunu atsauksmi.
    protected $fillable = [
        'reviewer_id',
        'reviewee_id',
        'job_submission_id',
        'rating',
        'comment',
    ];

    // Definē saiti ar lietotāju, kurš uzrakstīja atsauksmi.
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // Definē saiti ar lietotāju, kurš saņēma atsauksmi.
    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    // Definē saiti ar pieteikumu, pēc kura atsauksme tika izveidota.
    public function submission()
    {
        return $this->belongsTo(JobSubmission::class, 'job_submission_id');
    }
}
