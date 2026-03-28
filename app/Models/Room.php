<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_number',
        'building_id',
        'description',
        'status',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_room')->withTimestamps();
    }
}
