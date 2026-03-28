<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'image_path',
        'status',
        'unavailable_periods',
    ];

    protected $casts = [
        'unavailable_periods' => 'array',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_teacher')->withTimestamps();
    }
}
