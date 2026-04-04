<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherTermStatus extends Model
{
    protected $fillable = [
        'teacher_id', 'academic_year_id', 'semester_id',
        'status', 'can_be_scheduled',
        'max_periods_per_day', 'max_periods_per_week',
        'effective_from', 'effective_until',
        'notes', 'unavailable_periods',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'can_be_scheduled' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'unavailable_periods' => 'array',
    ];

    const STATUS_AVAILABLE = 'available';
    const STATUS_UNAVAILABLE = 'unavailable';
    const STATUS_LEAVE = 'leave';
    const STATUS_PARTIAL = 'partial';
    const STATUS_TRANSFERRED = 'transferred';
    const STATUS_RESIGNED_TERM = 'resigned_term';

    const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_UNAVAILABLE,
        self::STATUS_LEAVE,
        self::STATUS_PARTIAL,
        self::STATUS_TRANSFERRED,
        self::STATUS_RESIGNED_TERM,
    ];

    const SCHEDULABLE_STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_PARTIAL,
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function logs()
    {
        return $this->hasMany(TeacherTermStatusLog::class);
    }
}
