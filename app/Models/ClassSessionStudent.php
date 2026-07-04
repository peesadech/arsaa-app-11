<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSessionStudent extends Model
{
    protected $fillable = [
        'class_session_id', 'student_id', 'attendance_status_id',
        'arrival_time', 'remark', 'created_by',
    ];

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class);
    }
}
