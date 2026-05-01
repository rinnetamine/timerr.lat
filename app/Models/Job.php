<?php

// Šis fails apraksta darba sludinājuma datus, tā attiecības un noklusējuma attēlu izvēli.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    protected $table = 'job_listings';

    // Šie lauki drīkst tikt aizpildīti, veidojot vai labojot darba sludinājumu.
    protected $fillable = [
        'title',
        'description',
        'time_credits',
        'category',
        'image_path',
        'user_id'
    ];

    // Definē saiti ar lietotāju, kuram pieder sludinājums.
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Definē saiti ar darba pieteikumiem.
    public function submissions()
    {
        return $this->hasMany('App\Models\JobSubmission', 'job_listing_id');
    }

    // Nosaka galveno kategoriju no pilnā kategorijas identifikatora.
    public function categoryRoot()
    {
        return explode('.', $this->category ?: 'other')[0] ?: 'other';
    }

    // Atgriež sludinājuma attēla adresi, izmantojot augšupielādētu vai noklusējuma attēlu.
    public function imageUrl()
    {
        if ($this->image_path) {
            if (str_starts_with($this->image_path, 'images/')) {
                if (str_starts_with($this->image_path, 'images/job-defaults/')) {
                    return '/' . ltrim($this->defaultImagePath(), '/');
                }

                return '/' . ltrim($this->image_path, '/');
            }

            return '/storage/' . ltrim($this->image_path, '/');
        }

        return '/' . ltrim($this->defaultImagePath(), '/');
    }

    // Nosaka noklusējuma darba attēla faila ceļu.
    public function defaultImagePath()
    {
        return self::defaultImagePathForCategory($this->category ?: 'other');
    }

    // Atgriež noklusējuma darba attēla URL adresi.
    public function defaultImageUrl()
    {
        return '/' . ltrim($this->defaultImagePath(), '/');
    }

    // Izvēlas noklusējuma attēla ceļu pēc kategorijas galvenās grupas.
    public static function defaultImagePathForCategory(string $category)
    {
        $root = explode('.', $category ?: 'other')[0] ?: 'other';

        return 'images/job-defaults/' . $root . '.svg';
    }
}
