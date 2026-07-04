<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSessionTeachingLog extends Model
{
    protected $fillable = [
        'class_session_id', 'topic', 'content', 'notes', 'problems', 'assigned_work', 'created_by',
    ];

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }
}
