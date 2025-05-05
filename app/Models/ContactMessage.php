<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    // fillable attributes for contact messages
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'user_id'
    ];

    // attribute casts for date fields
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // relationship to the user who sent the message
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
