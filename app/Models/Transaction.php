<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    // fillable attributes for transactions
    protected $fillable = [
        'user_id',
        'amount',
        'description'
    ];

    // relationship to the user who owns the transaction
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
