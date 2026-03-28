<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag',
        'direction',
        'is_default',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public static function getActive()
    {
        return static::where('status', 1)->orderBy('sort_order')->get();
    }

    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }
}
