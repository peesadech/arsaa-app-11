<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConductCriterion extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'name_cn', 'max_score', 'sort_order', 'is_active'];

    protected $casts = [
        'max_score' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
