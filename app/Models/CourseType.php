<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseType extends Model
{
    protected $fillable = [
        'name_th',
        'name_en',
        'description',
        'grading_scheme_id',
        'status',
    ];

    public function gradingScheme()
    {
        return $this->belongsTo(GradingScheme::class, 'grading_scheme_id');
    }
}
