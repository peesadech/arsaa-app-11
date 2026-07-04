<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceStatus extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name_th', 'name_en', 'status_type',
        'is_count_as_present', 'is_count_as_absent', 'is_late', 'is_leave', 'is_require_remark',
        'color', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_count_as_present' => 'boolean',
        'is_count_as_absent'  => 'boolean',
        'is_late'             => 'boolean',
        'is_leave'            => 'boolean',
        'is_require_remark'   => 'boolean',
        'is_active'           => 'boolean',
        'sort_order'          => 'integer',
    ];

    /** Localized name (falls back to Thai). */
    public function getNameAttribute(): string
    {
        if (app()->getLocale() === 'en' && !empty($this->name_en)) {
            return $this->name_en;
        }
        return $this->name_th ?: ($this->name_en ?: $this->code);
    }

    /** Active statuses ordered for pickers. */
    public static function active()
    {
        return static::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
    }
}
