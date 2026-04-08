<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_number',
        'building_id',
        'floor_id',
        'description',
        'unavailable_periods',
        'status',
    ];

    protected $casts = [
        'unavailable_periods' => 'array',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_room')->withTimestamps();
    }

    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }
}
