<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenedCourse extends Model
{
    protected $fillable = ['academic_year_id', 'semester_id', 'grade_id', 'classroom_id', 'course_id'];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }

    public function scoreItems()
    {
        return $this->hasMany(ScoreItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function studentScores()
    {
        return $this->hasMany(StudentScore::class);
    }

    /** สัดส่วน/น้ำหนักของรายวิชานี้ ตามระดับชั้น+ปี+เทอม (คืน null ถ้ายังไม่กำหนด) */
    public function subjectWeight(): ?float
    {
        $weight = CourseWeight::where('academic_year_id', $this->academic_year_id)
            ->where('semester_id', $this->semester_id)
            ->where('grade_id', $this->grade_id)
            ->where('course_id', $this->course_id)
            ->value('weight');

        return $weight !== null ? (float) $weight : null;
    }
}
