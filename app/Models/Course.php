<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name',
        'grade_id',
        'semester_id',
        'subject_group_id',
        'status',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function subjectGroup()
    {
        return $this->belongsTo(SubjectGroup::class, 'subject_group_id');
    }
}
