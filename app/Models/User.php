<?php

// Šis fails apraksta lietotāju, viņa lomu, kredītus, profila attēlu un saistītos sistēmas ierakstus.
// Īpaši svarīga ir administratora pārbaude, konta bloķēšana un kredītu korekciju reģistrēšana.

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

    // Noklusējuma profila attēli tiek izmantoti, ja lietotājs nav augšupielādējis savu attēlu.
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

    // Šie lauki drīkst tikt masveidā aizpildīti no validētiem datiem.
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

    // Jaunam lietotājam tiek piešķirta sākotnējā kredītu bilance un parasta lietotāja loma.
    protected $attributes = [
        'time_credits' => 10,
        'role' => 'user',
        'is_banned' => false
    ];

    // Tukšs guarded saraksts ļauj izmantot masveida aizpildi atļautajiem laukiem.
    protected $guarded = [];

    // Šie lauki netiek parādīti serializētā lietotāja datos.
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Definē automātisku datu tipu pārveidošanu lietotāja modelī.
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
        ];
    }

    // Definē lietotāja izveidoto darbu saiti.
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    // Definē lietotāja saņemto atsauksmju saiti.
    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    // Definē lietotāja uzrakstīto atsauksmju saiti.
    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }
    
    // Pārbauda, vai lietotājam ir administratora loma.
    public function isAdmin()
    {
        // Administratora loma tiek noteikta pēc role lauka.
        return $this->role === 'admin';
    }

    // Definē lietotāja kredītu darījumu vēstures saiti.
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Saskaita lietotājam neizlasītos ziņojumus.
    public function unreadMessagesCount()
    {
        return \App\Models\Message::where('recipient_id', $this->id)->whereNull('read_at')->count();
    }

    // Definē lietotāja iesniegto pieteikumu saiti.
    public function jobSubmissions()
    {
        return $this->hasMany(JobSubmission::class);
    }

    // Atlasa lietotāja apstiprinātos un pabeigtos darbus.
    public function completedJobs()
    {
        return $this->hasMany(JobSubmission::class)->where('status', JobSubmission::STATUS_APPROVED);
    }

    // Saskaita lietotāja pabeigtos darbus.
    public function completedJobsCount()
    {
        return $this->completedJobs()->count();
    }

    // Izveido lietotāja iniciāļus profila attēla aizvietošanai.
    public function initials()
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    // Atgriež lietotāja profila attēla adresi.
    public function avatarUrl()
    {
        if (!$this->avatar_path) {
            return $this->defaultAvatarUrl();
        }

        if (str_starts_with($this->avatar_path, 'images/')) {
            return '/' . ltrim($this->avatar_path, '/');
        }

        return '/storage/' . ltrim($this->avatar_path, '/');
    }

    // Atgriež noklusējuma profila attēla adresi.
    public function defaultAvatarUrl()
    {
        return '/' . ltrim(self::defaultAvatarForSeed($this->email ?? $this->id), '/');
    }

    // Atgriež visus pieejamos noklusējuma profila attēlus.
    public static function defaultAvatarOptions()
    {
        return self::DEFAULT_AVATARS;
    }

    // Izvēlas stabilu noklusējuma profila attēlu pēc lietotāja identifikatora vai e-pasta.
    public static function defaultAvatarForSeed(int|string $seed)
    {
        $avatars = self::defaultAvatarOptions();

        return $avatars[abs(crc32((string) $seed)) % count($avatars)];
    }

    // Atgriež statiskos testa kontu e-pastus, kurus izmanto sākuma dati.
    public static function staticSeedEmails()
    {
        return self::STATIC_SEED_EMAILS;
    }

    // Pārbauda, vai lietotājs ir bloķēts.
    public function isBanned()
    {
        return $this->is_banned;
    }

    // Bloķē lietotāju un saglabā bloķēšanas iemeslu.
    public function ban($reason = null)
    {
        $this->update([
            'is_banned' => true,
            'ban_reason' => $reason,
            'banned_at' => now()
        ]);
    }

    // Atceļ lietotāja bloķēšanu.
    public function unban()
    {
        $this->update([
            'is_banned' => false,
            'ban_reason' => null,
            'banned_at' => null
        ]);
    }

    // Koriģē lietotāja kredītu bilanci un reģistrē darījumu vēsturē.
    public function adjustCredits($amount, $description = null)
    {
        $oldCredits = $this->time_credits;
        $this->increment('time_credits', $amount);
        
        // Kredītu korekcija tiek saglabāta darījumu vēsturē.
        \App\Models\Transaction::create([
            'user_id' => $this->id,
            'amount' => $amount,
            'description' => $description ?? "Credit adjustment by admin",
        ]);
        
        return $this->fresh();
    }
}
