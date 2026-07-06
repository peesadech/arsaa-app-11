<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenedClassroom extends Model
{
    protected $fillable = ['academic_year_id', 'semester_id', 'grade_id', 'classroom_id'];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    /** ครูประจำชั้น (หลัก/ร่วม) — เรียงหลักก่อนร่วม */
    public function homeroomTeachers()
    {
        return $this->belongsToMany(Teacher::class, 'opened_classroom_teacher')
            ->withPivot('role', 'sort_order')
            ->withTimestamps()
            ->orderByRaw("FIELD(opened_classroom_teacher.role, 'main', 'co')")
            ->orderBy('opened_classroom_teacher.sort_order');
    }

    const HOMEROOM_ROLES = ['main' => 'ครูประจำชั้น', 'co' => 'ครูประจำชั้นร่วม'];
}
