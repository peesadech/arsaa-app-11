<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalSchedule extends Model
{
    protected $table = 'global_schedules';

    protected $fillable = [
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

    public function educationLevel()
    {
        return $this->belongsTo(EducationLevel::class);
    }
}
