<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDocument extends Model
{
    protected $fillable = [
        'student_id', 'document_type_id', 'is_received', 'file_path', 'note',
    ];

    protected $casts = [
        'is_received' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function documentType()
    {
        return $this->belongsTo(MasterOption::class, 'document_type_id');
    }
}
