<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentGuardian extends Model
{
    protected $fillable = [
        'student_id', 'guardian_type_id', 'name', 'name_cn', 'age',
        'race_id', 'nationality_id', 'religion_id', 'living_status',
        'address', 'phone', 'occupation', 'workplace_address',
        'relationship', 'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    const LIVING_STATUSES = ['alive', 'deceased', 'together', 'divorced', 'other'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function guardianType()
    {
        return $this->belongsTo(MasterOption::class, 'guardian_type_id');
    }

    public function race()
    {
        return $this->belongsTo(MasterOption::class, 'race_id');
    }

    public function nationality()
    {
        return $this->belongsTo(MasterOption::class, 'nationality_id');
    }

    public function religion()
    {
        return $this->belongsTo(MasterOption::class, 'religion_id');
    }
}
