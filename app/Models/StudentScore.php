<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentScore extends Model
{
    protected $fillable = [
        'student_id', 'opened_course_id', 'teacher_id',
        'score_collect', 'score_midterm', 'score_final',
        'total_score', 'grade', 'result_status', 'remark', 'updated_by',
    ];

    protected $casts = [
        'score_collect' => 'decimal:2',
        'score_midterm' => 'decimal:2',
        'score_final' => 'decimal:2',
        'total_score' => 'decimal:2',
    ];

    const RESULT_PASS = 'pass';
    const RESULT_FAIL = 'fail';

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function openedCourse()
    {
        return $this->belongsTo(OpenedCourse::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
