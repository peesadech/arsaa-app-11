<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSubstitution extends Model
{
    protected $fillable = [
        'solution_id', 'timetable_entry_id', 'opened_course_id',
        'from_teacher_id', 'to_teacher_id', 'action',
        'day', 'period', 'reason', 'created_by',
    ];

    const ACTION_SUBSTITUTE = 'substitute';
    const ACTION_UNASSIGN = 'unassign';

    public function solution()
    {
        return $this->belongsTo(TimetableSolution::class, 'solution_id');
    }

    public function entry()
    {
        return $this->belongsTo(TimetableEntry::class, 'timetable_entry_id');
    }

    public function openedCourse()
    {
        return $this->belongsTo(OpenedCourse::class);
    }

    public function fromTeacher()
    {
        return $this->belongsTo(Teacher::class, 'from_teacher_id');
    }

    public function toTeacher()
    {
        return $this->belongsTo(Teacher::class, 'to_teacher_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
