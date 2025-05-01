<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Job;
use App\Models\Transaction;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'time_credits',
        'role'
    ];

    // default values for new users
    protected $attributes = [
        'time_credits' => 0,
        'role' => 'user'
    ];

    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // user has many job listings they've created
    public function jobs()
    {
        // one-to-many relationship with job model
        return $this->hasMany(Job::class);
    }
    
    // check if user has admin role
    public function isAdmin()
    {
        // returns true if user role is 'admin', false otherwise
        return $this->role === 'admin';
    }
    
    // user has many credit transactions
    public function transactions()
    {
        // one-to-many relationship with transaction model
        return $this->hasMany(Transaction::class);
    }
}
