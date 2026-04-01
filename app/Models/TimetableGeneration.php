<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableGeneration extends Model
{
    protected $fillable = [
        'academic_year_id', 'semester_id', 'user_id', 'status',
        'population_size', 'max_generations', 'current_generation',
        'solutions_requested', 'config', 'scope', 'error_message',
        'started_at', 'completed_at',
    ];

    protected $casts = [
        'config' => 'array',
        'scope' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function solutions()
    {
        return $this->hasMany(TimetableSolution::class, 'generation_id');
    }

    public function selectedSolution()
    {
        return $this->hasOne(TimetableSolution::class, 'generation_id')->where('is_selected', true);
    }
}
