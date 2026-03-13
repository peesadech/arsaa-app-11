<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionType extends Model
{
    protected $primaryKey = 'permissionType_id';

    protected $fillable = [
        'permissionType_name',
        'permissionType_image_path',
    ];
}
