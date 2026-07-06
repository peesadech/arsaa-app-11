<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentBehaviorRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id', 'academic_year_id', 'semester_id', 'grade_id', 'classroom_id',
        'behavior_score_id', 'type', 'name', 'score', 'note', 'recorded_by', 'recorded_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'recorded_at' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
