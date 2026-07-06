<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentScoreItem extends Model
{
    protected $fillable = [
        'score_item_id', 'student_id', 'score', 'updated_by',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function scoreItem()
    {
        return $this->belongsTo(ScoreItem::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
