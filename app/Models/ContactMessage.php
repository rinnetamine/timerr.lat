<?php

// Šis fails apraksta no kontaktformas saņemtos ziņojumus.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    // Šie lauki drīkst tikt aizpildīti no validētiem kontaktformas datiem.
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'user_id'
    ];

    // Datuma lauki tiek pārvērsti Carbon objektos ērtākai attēlošanai.
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Definē saiti ar lietotāju, kurš nosūtīja ziņojumu.
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
