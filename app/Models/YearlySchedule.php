<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YearlySchedule extends Model
{
    protected $table = 'yearly_schedules';

    protected $fillable = [
        'academic_year_id',
        'semester_id',
        'education_level_id',
        'teaching_days',
        'start_time',
        'period_duration',
        'day_configs',
    ];

    protected $casts = [
        'teaching_days' => 'array',
        'day_configs'   => 'array',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function educationLevel()
    {
        return $this->belongsTo(EducationLevel::class);
    }
}
