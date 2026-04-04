<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherTermStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'teacher_term_status_id',
        'old_status', 'new_status',
        'old_can_be_scheduled', 'new_can_be_scheduled',
        'reason', 'changed_by', 'changed_at',
    ];

    protected $casts = [
        'old_can_be_scheduled' => 'boolean',
        'new_can_be_scheduled' => 'boolean',
        'changed_at' => 'datetime',
    ];

    public function termStatus()
    {
        return $this->belongsTo(TeacherTermStatus::class, 'teacher_term_status_id');
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
