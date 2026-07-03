<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAddress extends Model
{
    protected $fillable = [
        'student_id', 'type', 'house_no', 'moo',
        'subdistrict', 'district', 'province_id', 'postal_code',
    ];

    const TYPE_CURRENT = 'current';
    const TYPE_REGISTERED = 'registered';

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function province()
    {
        return $this->belongsTo(MasterOption::class, 'province_id');
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->house_no,
            $this->moo ? __('Moo') . ' ' . $this->moo : null,
            $this->subdistrict,
            $this->district,
            $this->province?->name_th,
            $this->postal_code,
        ]);

        return implode(' ', $parts);
    }
}
