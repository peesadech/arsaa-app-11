<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $fillable = [
        'name_th',
        'name_en',
        'description',
        'status',
    ];
}
