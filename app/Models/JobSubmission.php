<?php

// Šis fails apraksta darba pieteikumu, tā statusus, strīda statusus un saistītos ierakstus.
// Statusu konstantes palīdz kontrolieriem vienādi pārvaldīt pieteikuma dzīves ciklu.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Review;

class JobSubmission extends Model
{
    use HasFactory;

    protected $table = 'job_submissions';

    // Šie lauki drīkst tikt masveidā aizpildīti no validētiem pieteikuma datiem.
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

    // Datu tipu pārveidošana palīdz korekti apstrādāt datumus un loģiskās vērtības.
    protected $casts = [
        'admin_approved' => 'boolean',
        'is_frozen' => 'boolean',
        'dispute_resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Statusu konstantes apraksta pieteikuma galvenos dzīves cikla posmus.
    const STATUS_CLAIMED = 'claimed';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DECLINED = 'declined';
    const STATUS_ADMIN_REVIEW = 'admin_review';

    // Strīda statusi atsevišķi apraksta konflikta apstrādes stāvokli.
    const DISPUTE_NONE = 'none';
    const DISPUTE_REQUESTED = 'requested';
    const DISPUTE_UNDER_REVIEW = 'under_review';
    const DISPUTE_RESOLVED = 'resolved';

    // Definē saiti ar darbu, uz kuru attiecas pieteikums.
    public function jobListing()
    {
        return $this->belongsTo(Job::class, 'job_listing_id');
    }

    // Definē saiti ar lietotāju, kurš iesniedza pieteikumu.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Definē saiti ar pieteikumam pievienotajiem failiem.
    public function files()
    {
        return $this->hasMany(SubmissionFile::class);
    }

    // Definē saiti ar atsauksmi par konkrēto pieteikumu.
    public function review()
    {
        return $this->hasOne(Review::class, 'job_submission_id');
    }

    // Definē saiti ar lietotāju, kurš uzsāka strīdu.
    public function disputeInitiator()
    {
        return $this->belongsTo(User::class, 'dispute_initiated_by');
    }

    // Definē saiti ar administratoru, kurš atrisināja strīdu.
    public function disputeResolver()
    {
        return $this->belongsTo(User::class, 'dispute_resolved_by');
    }

    // Pārbauda, vai pieteikumam vēl drīkst atvērt strīdu.
    public function canBeDisputed()
    {
        // Ja strīds jau pastāv, to nevar atvērt atkārtoti.
        if ($this->dispute_status !== self::DISPUTE_NONE) {
            return false;
        }

        // Iesaistītajām pusēm strīdu drīkst atvērt, ja tas vēl nav sākts.
        return true;
    }

    // Nosaka, vai pieteikums pašlaik ir iesaldēts.
    public function isFrozen()
    {
        return $this->is_frozen;
    }

    // Iesaldē pieteikumu un saglabā iesaldēšanas iemeslu.
    public function freeze($reason = null)
    {
        $this->is_frozen = true;
        $this->freeze_reason = $reason;
        $this->save();
    }

    // Atceļ pieteikuma iesaldēšanu.
    public function unfreeze()
    {
        $this->is_frozen = false;
        $this->freeze_reason = null;
        $this->save();
    }
}
