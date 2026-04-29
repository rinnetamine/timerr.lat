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
        'image_path',
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
        return $this->hasMany('App\Models\JobSubmission', 'job_listing_id');
    }

    public function categoryRoot()
    {
        return explode('.', $this->category ?: 'other')[0] ?: 'other';
    }

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

    public function defaultImagePath()
    {
        return self::defaultImagePathForCategory($this->category ?: 'other');
    }

    public function defaultImageUrl()
    {
        return '/' . ltrim($this->defaultImagePath(), '/');
    }

    public static function defaultImagePathForCategory(string $category)
    {
        $root = explode('.', $category ?: 'other')[0] ?: 'other';

        return 'images/job-defaults/' . $root . '.svg';
    }
}
