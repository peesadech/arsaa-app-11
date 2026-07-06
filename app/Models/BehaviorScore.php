<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BehaviorScore extends Model
{
    use SoftDeletes;

    protected $fillable = ['type', 'name', 'score', 'sort_order', 'is_active'];

    protected $casts = [
        'score' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    const TYPE_MERIT = 'merit';     // ความดี (คะแนน > 0)
    const TYPE_DEMERIT = 'demerit'; // ความชั่ว (คะแนน < 0)

    const TYPES = [self::TYPE_MERIT, self::TYPE_DEMERIT];

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
