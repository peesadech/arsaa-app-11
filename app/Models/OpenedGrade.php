<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenedGrade extends Model
{
    protected $fillable = ['academic_year_id', 'semester_id', 'grade_id'];

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
}
