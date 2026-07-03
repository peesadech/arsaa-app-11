<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeSetting extends Model
{
    protected $fillable = ['grade', 'min_score', 'max_score', 'is_pass', 'sort_order'];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'is_pass' => 'boolean',
    ];

    /**
     * หาเกรดจากคะแนนรวม — คืน null ถ้าไม่เข้าเกณฑ์ใดเลย
     */
    public static function gradeForScore(float $score): ?self
    {
        return static::where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->orderBy('sort_order')
            ->first();
    }
}
