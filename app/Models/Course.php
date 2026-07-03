<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name',
        'grade_id',
        'semester_id',
        'subject_group_id',
        'course_type_id',
        'grading_scheme_id',
        'periods_per_week',
        'periods_per_session',
        'preferred_days',
        'status',
    ];

    protected $casts = [
        'preferred_days' => 'array',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function subjectGroup()
    {
        return $this->belongsTo(SubjectGroup::class, 'subject_group_id');
    }

    public function courseType()
    {
        return $this->belongsTo(CourseType::class, 'course_type_id');
    }

    public function gradingScheme()
    {
        return $this->belongsTo(GradingScheme::class, 'grading_scheme_id');
    }

    /**
     * รูปแบบการคิดเกรดที่ใช้จริง — รายวิชา override ก่อน, ไม่มีก็ใช้ของประเภทวิชา
     */
    public function resolveGradingScheme(): ?GradingScheme
    {
        return $this->gradingScheme ?? $this->courseType?->gradingScheme;
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'course_teacher')->withTimestamps();
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'course_room')->withTimestamps();
    }
}
