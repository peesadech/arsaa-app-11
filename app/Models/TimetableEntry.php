<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableEntry extends Model
{
    protected $fillable = [
        'solution_id', 'opened_course_id', 'teacher_id', 'room_id',
        'day', 'period', 'is_locked',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function solution()
    {
        return $this->belongsTo(TimetableSolution::class, 'solution_id');
    }

    public function openedCourse()
    {
        return $this->belongsTo(OpenedCourse::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
