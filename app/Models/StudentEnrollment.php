<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    protected $fillable = [
        'student_id', 'academic_year_id', 'semester_id',
        'grade_id', 'classroom_id', 'status', 'enrolled_at', 'note', 'created_by',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
    ];

    const STATUS_ENROLLED = 'enrolled';
    const STATUS_MOVED = 'moved';
    const STATUS_LEFT = 'left';

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
