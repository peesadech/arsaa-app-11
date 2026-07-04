<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    const STATUS_OPEN      = 'OPEN';
    const STATUS_CLOSED    = 'CLOSED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_POSTPONED = 'POSTPONED';

    const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_CLOSED,
        self::STATUS_CANCELLED,
        self::STATUS_POSTPONED,
    ];

    protected $fillable = [
        'academic_year_id', 'semester_id', 'timetable_entry_id', 'opened_course_id',
        'course_id', 'grade_id', 'classroom_id', 'teacher_id',
        'session_date', 'day', 'period', 'start_time', 'end_time',
        'status', 'remark', 'created_by',
    ];

    protected $casts = [
        'session_date' => 'date',
        'day'          => 'integer',
        'period'       => 'integer',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function timetableEntry()
    {
        return $this->belongsTo(TimetableEntry::class);
    }

    public function openedCourse()
    {
        return $this->belongsTo(OpenedCourse::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function students()
    {
        return $this->hasMany(ClassSessionStudent::class);
    }

    public function teachingLog()
    {
        return $this->hasOne(ClassSessionTeachingLog::class);
    }

    public function homeworks()
    {
        return $this->hasMany(ClassSessionHomework::class)->latest();
    }

    public function assessments()
    {
        return $this->hasMany(ClassSessionAssessment::class)->latest();
    }

    public function files()
    {
        return $this->hasMany(ClassSessionFile::class)->latest();
    }
}
