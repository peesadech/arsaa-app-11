<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSessionHomework extends Model
{
    // "homework" เป็นคำนามนับไม่ได้ Laravel เลย guess ชื่อตารางผิด — ระบุให้ตรง migration
    protected $table = 'class_session_homeworks';

    protected $fillable = [
        'class_session_id', 'title', 'description', 'due_date', 'max_score', 'created_by',
    ];

    protected $casts = [
        'due_date'  => 'date',
        'max_score' => 'decimal:2',
    ];

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }
}
