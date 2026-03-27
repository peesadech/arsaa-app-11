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
}
