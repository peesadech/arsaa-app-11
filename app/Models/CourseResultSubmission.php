<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseResultSubmission extends Model
{
    protected $fillable = [
        'opened_course_id', 'status',
        'submitted_by', 'submitted_at', 'reviewed_by', 'reviewed_at',
        'approved_by', 'approved_at', 'published_by', 'published_at', 'reject_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_APPROVED = 'approved';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REJECTED = 'rejected';

    const LABELS = [
        'draft'     => 'ร่าง',
        'submitted' => 'ส่งแล้ว (รอตรวจ)',
        'reviewed'  => 'ตรวจแล้ว (รออนุมัติ)',
        'approved'  => 'อนุมัติแล้ว (รอเผยแพร่)',
        'published' => 'เผยแพร่แล้ว',
        'rejected'  => 'ตีกลับ',
    ];

    const BADGE_COLORS = [
        'draft'     => 'gray',
        'submitted' => 'amber',
        'reviewed'  => 'blue',
        'approved'  => 'blue',
        'published' => 'green',
        'rejected'  => 'red',
    ];

    public function openedCourse()
    {
        return $this->belongsTo(OpenedCourse::class);
    }

    public function submittedBy() { return $this->belongsTo(User::class, 'submitted_by'); }
    public function reviewedBy()  { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function approvedBy()  { return $this->belongsTo(User::class, 'approved_by'); }
    public function publishedBy() { return $this->belongsTo(User::class, 'published_by'); }

    public function statusLabel(): string
    {
        return self::LABELS[$this->status] ?? $this->status;
    }

    public function badgeColor(): string
    {
        return self::BADGE_COLORS[$this->status] ?? 'gray';
    }

    /** แก้คะแนนได้เมื่ออยู่สถานะ ร่าง หรือ ตีกลับ เท่านั้น (ครู); admin แก้ได้เสมอ */
    public function isLockedForTeacher(): bool
    {
        return !in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true);
    }
}
