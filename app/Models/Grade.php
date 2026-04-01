<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'education_level_id',
        'name_th',
        'name_en',
        'description',
        'status',
    ];

    public function educationLevel()
    {
        return $this->belongsTo(EducationLevel::class);
    }

    public function openedCourses()
    {
        return $this->hasMany(OpenedCourse::class);
    }
}
