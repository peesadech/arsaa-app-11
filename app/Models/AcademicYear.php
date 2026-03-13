<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'year',
        'status',
        'is_current_year',
    ];

    protected $casts = [
        'is_current_year' => 'boolean',
    ];
}
