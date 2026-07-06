<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseWeight extends Model
{
    protected $fillable = [
        'academic_year_id', 'semester_id', 'grade_id', 'course_id', 'weight', 'updated_by',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
}
