<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentScoreLog extends Model
{
    protected $fillable = [
        'student_score_id', 'action', 'from_value', 'to_value', 'reason', 'changed_by',
    ];

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
