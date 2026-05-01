<?php

// Šis fails apraksta laika kredītu kustības ierakstus lietotāju profilos.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    // Šie lauki drīkst tikt aizpildīti, reģistrējot jaunu kredītu darījumu.
    protected $fillable = [
        'user_id',
        'amount',
        'description'
    ];

    // Definē saiti ar lietotāju, kuram pieder darījums.
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
