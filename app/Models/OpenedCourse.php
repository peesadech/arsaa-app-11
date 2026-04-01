<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenedCourse extends Model
{
    protected $fillable = ['academic_year_id', 'semester_id', 'grade_id', 'classroom_id', 'course_id'];

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

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }
}
