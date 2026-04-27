<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Job;
use App\Models\Transaction;
use App\Models\Review;
use App\Models\JobSubmission;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const DEFAULT_AVATARS = [
        'images/avatar-defaults/aurora.svg',
        'images/avatar-defaults/circuit.svg',
        'images/avatar-defaults/comet.svg',
        'images/avatar-defaults/coral.svg',
        'images/avatar-defaults/dawn.svg',
        'images/avatar-defaults/ember.svg',
        'images/avatar-defaults/forest.svg',
        'images/avatar-defaults/glacier.svg',
        'images/avatar-defaults/harbor.svg',
        'images/avatar-defaults/iris.svg',
        'images/avatar-defaults/mint.svg',
        'images/avatar-defaults/solar.svg',
    ];

    public const STATIC_SEED_EMAILS = [
        'admin@timerr.lat',
        'user@timerr.lat',
        'user1@timerr.lat',
        'user2@timerr.lat',
        'user3@timerr.lat',
        'user4@timerr.lat',
        'user5@timerr.lat',
        'demo@local.test',
    ];

    // fillable attributes for user model
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'time_credits',
        'avatar_path',
        'role',
        'is_banned',
        'ban_reason',
        'banned_at'
    ];

    // default values for new users
    protected $attributes = [
        'time_credits' => 10,
        'role' => 'user',
        'is_banned' => false
    ];

    // guarded attributes (empty array means all attributes are fillable)
    protected $guarded = [];

    // hidden attributes for serialization
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // attribute casts for data type conversion
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
        ];
    }

    // relationship to job listings created by user
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    // reviews that this user has received
    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    // reviews that this user has given
    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }
    
    // check if user has admin privileges
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    // relationship to user's credit transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // count unread messages where this user is the recipient
    public function unreadMessagesCount()
    {
        return \App\Models\Message::where('recipient_id', $this->id)->whereNull('read_at')->count();
    }

    // relationship to job submissions (jobs the user has completed)
    public function jobSubmissions()
    {
        return $this->hasMany(JobSubmission::class);
    }

    // relationship to successfully completed jobs (approved submissions)
    public function completedJobs()
    {
        return $this->hasMany(JobSubmission::class)->where('status', JobSubmission::STATUS_APPROVED);
    }

    // count of successfully completed jobs
    public function completedJobsCount()
    {
        return $this->completedJobs()->count();
    }

    public function initials()
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    public function avatarUrl()
    {
        if (!$this->avatar_path) {
            return null;
        }

        if (str_starts_with($this->avatar_path, 'images/')) {
            return asset($this->avatar_path);
        }

        return asset('storage/' . $this->avatar_path);
    }

    public static function defaultAvatarOptions()
    {
        return self::DEFAULT_AVATARS;
    }

    public static function defaultAvatarForSeed(int|string $seed)
    {
        $avatars = self::defaultAvatarOptions();

        return $avatars[abs(crc32((string) $seed)) % count($avatars)];
    }

    public static function staticSeedEmails()
    {
        return self::STATIC_SEED_EMAILS;
    }

    // check if user is banned
    public function isBanned()
    {
        return $this->is_banned;
    }

    // ban the user
    public function ban($reason = null)
    {
        $this->update([
            'is_banned' => true,
            'ban_reason' => $reason,
            'banned_at' => now()
        ]);
    }

    // unban the user
    public function unban()
    {
        $this->update([
            'is_banned' => false,
            'ban_reason' => null,
            'banned_at' => null
        ]);
    }

    // adjust user credits
    public function adjustCredits($amount, $description = null)
    {
        $oldCredits = $this->time_credits;
        $this->increment('time_credits', $amount);
        
        // Create transaction record
        \App\Models\Transaction::create([
            'user_id' => $this->id,
            'amount' => $amount,
            'description' => $description ?? "Credit adjustment by admin",
        ]);
        
        return $this->fresh();
    }
}
