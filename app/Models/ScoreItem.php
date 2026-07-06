<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreItem extends Model
{
    protected $fillable = [
        'opened_course_id', 'category', 'name', 'full_score', 'weight',
        'counts_toward_total', 'sort_order', 'is_active', 'created_by',
    ];

    protected $casts = [
        'full_score' => 'decimal:2',
        'weight' => 'decimal:2',
        'counts_toward_total' => 'boolean',
        'is_active' => 'boolean',
    ];

    /** หมวดหมู่รายการคะแนน (key => ป้ายไทย) */
    const CATEGORIES = [
        'assignment' => 'ชิ้นงาน',
        'homework'   => 'การบ้าน',
        'quiz'       => 'ทดสอบย่อย',
        'practical'  => 'ปฏิบัติ',
        'midterm'    => 'กลางภาค',
        'final'      => 'ปลายภาค',
        'extra'      => 'คะแนนเพิ่มเติม',
        'attribute'  => 'คุณลักษณะ',
        'reading'    => 'อ่านคิดวิเคราะห์',
        'special'    => 'คะแนนพิเศษ',
        'other'      => 'อื่น ๆ',
    ];

    public function openedCourse()
    {
        return $this->belongsTo(OpenedCourse::class);
    }

    public function entries()
    {
        return $this->hasMany(StudentScoreItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** น้ำหนักจริงที่ใช้คิดคะแนนรวม: ถ้าไม่ตั้งน้ำหนัก ใช้คะแนนเต็ม (= คะแนนดิบ) */
    public function effectiveWeight(): float
    {
        return $this->weight !== null ? (float) $this->weight : (float) $this->full_score;
    }
}
