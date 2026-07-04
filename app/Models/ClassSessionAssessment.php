<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSessionAssessment extends Model
{
    const TYPES = ['quiz', 'assignment', 'participation', 'score'];

    protected $fillable = [
        'class_session_id', 'type', 'title', 'description', 'max_score', 'created_by',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
    ];

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }
}
