<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Floor extends Model
{
    protected $fillable = [
        'building_id',
        'name_th',
        'name_en',
        'description',
        'status',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }
}
