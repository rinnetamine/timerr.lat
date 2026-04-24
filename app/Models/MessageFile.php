<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size'
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function getUrlAttribute()
    {
        return route('messages.files.download', $this);
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf()
    {
        return $this->mime_type === 'application/pdf';
    }
}
