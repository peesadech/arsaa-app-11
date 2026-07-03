<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'password',
        'phone',
        'image_path',
        'status',
        'unavailable_periods',
    ];

    protected $casts = [
        'unavailable_periods' => 'array',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_teacher')->withTimestamps();
    }

    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }

    public function termStatuses()
    {
        return $this->hasMany(TeacherTermStatus::class);
    }

    public function termCourses()
    {
        return $this->hasMany(TeacherTermCourse::class);
    }

    public function coursesForTerm(int $yearId, int $semesterId)
    {
        return $this->termCourses()
            ->where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId);
    }

    public function termStatus(int $yearId, int $semesterId): ?TeacherTermStatus
    {
        return $this->termStatuses()
            ->where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->first();
    }

    /**
     * วิชาที่เปิดสอนในเทอมนั้นที่ครูคนนี้สอน (pivot global + วิชารายเทอม)
     */
    public function openedCoursesForTerm(int $yearId, int $semesterId)
    {
        $courseIds = $this->courses()->pluck('courses.id')
            ->merge(
                TeacherTermCourse::where('teacher_id', $this->id)
                    ->where('academic_year_id', $yearId)
                    ->where('semester_id', $semesterId)
                    ->pluck('course_id')
            )
            ->unique();

        return OpenedCourse::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->whereIn('course_id', $courseIds)
            ->with('course.subjectGroup', 'grade', 'classroom')
            ->get();
    }

    public function isSchedulableForTerm(int $yearId, int $semesterId): bool
    {
        if ($this->status !== 1) return false;

        $termStatus = $this->termStatus($yearId, $semesterId);
        if (!$termStatus) return true; // no record = available

        return $termStatus->can_be_scheduled;
    }
}
