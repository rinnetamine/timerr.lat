<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'body',
        'read_at'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function files()
    {
        return $this->hasMany(MessageFile::class);
    }

    /**
     * encrypt the message body when saving to the database
     */
    public function setBodyAttribute($value)
    {
        // if already encrypted or null, just set it
        if (is_null($value) || $value === '') {
            $this->attributes['body'] = $value;
            return;
        }

        try {
            $this->attributes['body'] = Crypt::encryptString($value);
        } catch (\Exception $e) {
            // fallback to raw value if encryption fails for any reason
            $this->attributes['body'] = $value;
        }
    }

    /**
     * decrypt the message body when accessing it
     */
    public function getBodyAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // value was not encrypted with Crypt::encryptString; return raw
            return $value;
        } catch (\Exception $e) {
            return $value;
        }
    }
}
