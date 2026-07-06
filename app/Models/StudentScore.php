<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentScore extends Model
{
    protected $fillable = [
        'student_id', 'opened_course_id', 'teacher_id',
        'score_collect', 'score_midterm', 'score_final',
        'total_score', 'grade', 'result_status', 'remark', 'updated_by',
        'special_result', 'is_override', 'override_reason', 'graded_by',
    ];

    protected $casts = [
        'score_collect' => 'decimal:2',
        'score_midterm' => 'decimal:2',
        'score_final' => 'decimal:2',
        'total_score' => 'decimal:2',
        'is_override' => 'boolean',
    ];

    const RESULT_PASS = 'pass';
    const RESULT_FAIL = 'fail';

    /** ผลพิเศษ (ตั้งเอง): ร มส มผ ผ ขส */
    const SPECIAL_RESULTS = ['ร', 'มส', 'มผ', 'ผ', 'ขส'];

    /** เกรดที่นับเป็น "ผ่าน" เมื่อเป็นผลพิเศษ */
    const SPECIAL_PASS = ['ผ'];

    /** เกรดสุดท้ายที่ใช้แสดง: ผลพิเศษ > เกรด */
    public function displayGrade(): string
    {
        return $this->special_result ?: ($this->grade ?? '-');
    }

    public function logs()
    {
        return $this->hasMany(StudentScoreLog::class)->latest();
    }

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
