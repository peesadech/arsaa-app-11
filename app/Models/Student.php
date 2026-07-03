<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'student_code', 'image_path', 'name_th', 'name_cn', 'citizen_id',
        'birth_date', 'race_id', 'nationality_id', 'religion_id', 'blood_type_id',
        'height', 'weight', 'chronic_disease', 'phone', 'mobile',
        'status', 'note', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'height' => 'decimal:1',
        'weight' => 'decimal:1',
    ];

    const STATUS_STUDYING = 'studying';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_RESIGNED = 'resigned';
    const STATUS_GRADUATED = 'graduated';

    const STATUSES = [
        self::STATUS_STUDYING,
        self::STATUS_SUSPENDED,
        self::STATUS_RESIGNED,
        self::STATUS_GRADUATED,
    ];

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
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

    public function bloodType()
    {
        return $this->belongsTo(MasterOption::class, 'blood_type_id');
    }

    public function addresses()
    {
        return $this->hasMany(StudentAddress::class);
    }

    public function addressOfType(string $type): ?StudentAddress
    {
        return $this->addresses->firstWhere('type', $type);
    }

    public function guardians()
    {
        return $this->hasMany(StudentGuardian::class);
    }

    public function educationHistories()
    {
        return $this->hasMany(StudentEducationHistory::class);
    }

    public function documents()
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function scores()
    {
        return $this->hasMany(StudentScore::class);
    }

    /**
     * สร้างรหัสนักเรียนอัตโนมัติ เช่น 68-0001 (ปี พ.ศ. 2 หลัก + running)
     */
    public static function generateCode(): string
    {
        $prefix = substr((string) (now()->year + 543), -2);
        $last = static::where('student_code', 'like', $prefix . '-%')
            ->orderByDesc('student_code')
            ->value('student_code');

        $next = $last ? ((int) substr($last, 3)) + 1 : 1;

        return $prefix . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
