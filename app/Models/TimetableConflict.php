<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableConflict extends Model
{
    protected $fillable = [
        'solution_id', 'type', 'severity', 'day', 'period', 'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function solution()
    {
        return $this->belongsTo(TimetableSolution::class, 'solution_id');
    }
}
