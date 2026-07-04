<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSessionFile extends Model
{
    const KIND_FILE = 'file';
    const KIND_PHOTO = 'photo';

    protected $fillable = [
        'class_session_id', 'kind', 'path', 'original_name', 'mime', 'size', 'created_by',
    ];

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
