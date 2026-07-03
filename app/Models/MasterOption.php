<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterOption extends Model
{
    protected $fillable = ['type', 'name_th', 'name_en', 'name_cn', 'sort_order', 'status'];

    const TYPE_NATIONALITY = 'nationality';   // ใช้ทั้งเชื้อชาติและสัญชาติ
    const TYPE_RELIGION = 'religion';
    const TYPE_BLOOD_TYPE = 'blood_type';
    const TYPE_DOCUMENT_TYPE = 'document_type';
    const TYPE_GUARDIAN_TYPE = 'guardian_type';
    const TYPE_PROVINCE = 'province';

    const TYPES = [
        self::TYPE_NATIONALITY,
        self::TYPE_RELIGION,
        self::TYPE_BLOOD_TYPE,
        self::TYPE_DOCUMENT_TYPE,
        self::TYPE_GUARDIAN_TYPE,
        self::TYPE_PROVINCE,
    ];

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type)->where('status', 1)
            ->orderBy('sort_order')->orderBy('name_th');
    }

    public static function options(string $type)
    {
        return static::ofType($type)->get();
    }
}
