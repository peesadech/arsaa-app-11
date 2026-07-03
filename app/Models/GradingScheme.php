<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingScheme extends Model
{
    protected $fillable = ['name', 'result_type', 'description', 'status'];

    const RESULT_TYPE_GRADE = 'grade';         // ผลเป็นเกรด เช่น A, B, C
    const RESULT_TYPE_PASS_FAIL = 'pass_fail'; // ผลเป็น ผ่าน/ไม่ผ่าน

    const RESULT_TYPES = [self::RESULT_TYPE_GRADE, self::RESULT_TYPE_PASS_FAIL];

    public function details()
    {
        return $this->hasMany(GradingSchemeDetail::class)->orderBy('sort_order');
    }
}
