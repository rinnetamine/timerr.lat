<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Job;
use App\Models\Transaction;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // fillable attributes for user model
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
        'time_credits' => 10,
        'role' => 'user'
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
        ];
    }

    // relationship to job listings created by user
    public function jobs()
    {
        return $this->hasMany(Job::class);
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
}
