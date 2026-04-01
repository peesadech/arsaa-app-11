<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableSolution extends Model
{
    protected $fillable = [
        'generation_id', 'rank', 'fitness_score',
        'hard_violations', 'soft_violations', 'fitness_breakdown', 'is_selected',
    ];

    protected $casts = [
        'fitness_breakdown' => 'array',
        'is_selected' => 'boolean',
        'fitness_score' => 'decimal:4',
    ];

    public function generation()
    {
        return $this->belongsTo(TimetableGeneration::class, 'generation_id');
    }

    public function entries()
    {
        return $this->hasMany(TimetableEntry::class, 'solution_id');
    }

    public function conflicts()
    {
        return $this->hasMany(TimetableConflict::class, 'solution_id');
    }
}
