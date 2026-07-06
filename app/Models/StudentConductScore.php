<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentConductScore extends Model
{
    protected $fillable = [
        'student_id', 'academic_year_id', 'semester_id', 'conduct_criterion_id', 'score', 'updated_by',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function criterion()
    {
        return $this->belongsTo(ConductCriterion::class, 'conduct_criterion_id');
    }
}
