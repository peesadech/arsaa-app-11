<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingSchemeDetail extends Model
{
    protected $fillable = ['grading_scheme_id', 'min_score', 'max_score', 'result_th', 'result_en', 'result_cn', 'description', 'sort_order'];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    public function gradingScheme()
    {
        return $this->belongsTo(GradingScheme::class);
    }

    /**
     * ผลตามภาษาปัจจุบัน (fallback เป็นไทย)
     */
    public function getResultAttribute(): string
    {
        return match (app()->getLocale()) {
            'en' => $this->result_en ?: $this->result_th,
            'zh' => $this->result_cn ?: $this->result_th,
            default => $this->result_th,
        };
    }

    /**
     * ข้อความช่วงคะแนน เช่น "80 - 100"
     */
    public function getConditionTextAttribute(): string
    {
        return ($this->min_score + 0) . ' - ' . ($this->max_score + 0);
    }
}
