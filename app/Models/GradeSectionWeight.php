<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeSectionWeight extends Model
{
    protected $fillable = [
        'academic_year_id', 'semester_id', 'grade_id',
        'midterm_weight', 'final_weight', 'collect_weight',
    ];

    protected $casts = [
        'midterm_weight' => 'decimal:2',
        'final_weight' => 'decimal:2',
        'collect_weight' => 'decimal:2',
    ];

    /** ค่าตั้งต้นสำหรับปี+เทอม+ชั้น (fallback 35/35/30) */
    public static function forGrade(int $yearId, int $semesterId, int $gradeId): self
    {
        return static::firstOrNew(
            ['academic_year_id' => $yearId, 'semester_id' => $semesterId, 'grade_id' => $gradeId],
            ['midterm_weight' => 35, 'final_weight' => 35, 'collect_weight' => 30]
        );
    }
}
