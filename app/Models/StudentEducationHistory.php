<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEducationHistory extends Model
{
    protected $fillable = [
        'student_id', 'school_name', 'school_location',
        'last_level', 'gpa', 'graduated_at', 'note',
    ];

    protected $casts = [
        'gpa' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
