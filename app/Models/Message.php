<?php

// Šis fails apraksta privāto ziņojumu datus, pielikumus un ziņojuma satura šifrēšanu.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class Message extends Model
{
    use HasFactory;

    // Šie lauki drīkst tikt aizpildīti, veidojot ziņojumu no validēta pieprasījuma.
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'body',
        'attachment_name',
        'attachment_path',
        'attachment_mime_type',
        'attachment_size',
        'read_at'
    ];

    // Definē saiti ar ziņojuma sūtītāju.
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Definē saiti ar ziņojuma saņēmēju.
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    // Pārbauda, vai ziņojumam ir pievienots fails.
    public function hasAttachment()
    {
        return filled($this->attachment_path);
    }

    // Izveido pielikuma lejupielādes adresi skatiem.
    public function getAttachmentUrlAttribute()
    {
        return $this->hasAttachment() ? route('messages.files.download', $this) : null;
    }

    // Pārveido pielikuma izmēru cilvēkam saprotamā formātā.
    public function getAttachmentFormattedSizeAttribute()
    {
        $bytes = (int) $this->attachment_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Nosaka, vai pielikumu var rādīt kā attēlu pārlūkā.
    public function attachmentIsImage()
    {
        return str_starts_with((string) $this->attachment_mime_type, 'image/');
    }

    // Nosaka, vai pielikums ir PDF dokuments.
    public function attachmentIsPdf()
    {
        return $this->attachment_mime_type === 'application/pdf';
    }

    // Šifrē ziņojuma saturu pirms saglabāšanas datubāzē.
    public function setBodyAttribute($value)
    {
        // Tukšs teksts netiek šifrēts, lai faila ziņojumi bez teksta paliktu derīgi.
        if (is_null($value) || $value === '') {
            $this->attributes['body'] = $value;
            return;
        }

        try {
            $this->attributes['body'] = Crypt::encryptString($value);
        } catch (\Exception $e) {
            // Ja šifrēšana neizdodas, tiek saglabāta sākotnējā vērtība, lai nezaudētu ziņojumu.
            $this->attributes['body'] = $value;
        }
    }

    // Atšifrē ziņojuma saturu piekļuves brīdī.
    public function getBodyAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Vecāki vai testa ieraksti var būt nešifrēti, tāpēc tos atgriež bez izmaiņām.
            return $value;
        } catch (\Exception $e) {
            return $value;
        }
    }
}
