<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherTermCourse extends Model
{
    protected $fillable = [
        'teacher_id', 'academic_year_id', 'semester_id', 'course_id',
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

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
