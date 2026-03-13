<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrentAcademicSetting extends Model
{
    protected $fillable = [
        'academic_year_id',
        'semester_id',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
