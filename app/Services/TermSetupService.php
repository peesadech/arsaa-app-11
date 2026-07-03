<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\EducationLevel;
use App\Models\OpenedClassroom;
use App\Models\OpenedCourse;
use App\Models\OpenedGrade;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\TeacherTermCourse;
use App\Models\TeacherTermStatus;
use App\Models\YearlySchedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TermSetupService
{
    /**
     * สถานะความพร้อมของเทอม — ใช้แสดง checklist ว่าตั้งค่าอะไรไปแล้วบ้าง
     */
    public function readiness(int $yearId, int $semesterId): array
    {
        $totalLevels = EducationLevel::where('status', 1)->count();
        $activeTeachers = Teacher::where('status', 1)->count();

        return [
            'yearly_schedules' => [
                'count' => YearlySchedule::where('academic_year_id', $yearId)->where('semester_id', $semesterId)->count(),
                'total' => $totalLevels,
            ],
            'opened_grades' => [
                'count' => OpenedGrade::where('academic_year_id', $yearId)->where('semester_id', $semesterId)->count(),
            ],
            'opened_classrooms' => [
                'count' => OpenedClassroom::where('academic_year_id', $yearId)->where('semester_id', $semesterId)->count(),
            ],
            'opened_courses' => [
                'count' => OpenedCourse::where('academic_year_id', $yearId)->where('semester_id', $semesterId)->count(),
            ],
            'teacher_term_statuses' => [
                'count' => TeacherTermStatus::where('academic_year_id', $yearId)->where('semester_id', $semesterId)->count(),
                'total' => $activeTeachers,
            ],
            'teacher_term_courses' => [
                'count' => TeacherTermCourse::where('academic_year_id', $yearId)->where('semester_id', $semesterId)->distinct('teacher_id')->count('teacher_id'),
            ],
        ];
    }

    /**
     * รายการ key "yearId-semesterId" ของเทอมที่มีข้อมูลแล้ว — ใช้กันการสร้างเทอมซ้ำ
     */
    public function termKeysWithData(): array
    {
        $keys = [];

        $models = [
            YearlySchedule::class,
            OpenedGrade::class,
            OpenedClassroom::class,
            OpenedCourse::class,
            TeacherTermStatus::class,
            TeacherTermCourse::class,
        ];

        foreach ($models as $model) {
            $rows = $model::select('academic_year_id', 'semester_id')->distinct()->get();
            foreach ($rows as $row) {
                $keys[$row->academic_year_id . '-' . $row->semester_id] = true;
            }
        }

        return array_keys($keys);
    }

    public function hasData(int $yearId, int $semesterId): bool
    {
        return in_array($yearId . '-' . $semesterId, $this->termKeysWithData());
    }

    /**
     * รายการ ปี/เทอม ที่เคยมีข้อมูล (ไว้เลือกเป็นต้นทาง clone) — ล่าสุดอยู่บน
     */
    public function listSourceTerms(int $excludeYearId, int $excludeSemesterId): Collection
    {
        $terms = collect();

        foreach ([YearlySchedule::class, OpenedGrade::class, TeacherTermStatus::class] as $model) {
            $terms = $terms->merge(
                $model::select('academic_year_id', 'semester_id')->distinct()->get()
                    ->map(fn($r) => ['academic_year_id' => $r->academic_year_id, 'semester_id' => $r->semester_id])
            );
        }

        $years = AcademicYear::pluck('year', 'id');
        $semesters = Semester::pluck('semester_number', 'id');

        return $terms->unique(fn($t) => $t['academic_year_id'] . '-' . $t['semester_id'])
            ->reject(fn($t) => $t['academic_year_id'] == $excludeYearId && $t['semester_id'] == $excludeSemesterId)
            ->map(fn($t) => [
                'academic_year_id' => $t['academic_year_id'],
                'semester_id' => $t['semester_id'],
                'year' => $years[$t['academic_year_id']] ?? '?',
                'semester_number' => $semesters[$t['semester_id']] ?? '?',
                'summary' => $this->readiness($t['academic_year_id'], $t['semester_id']),
            ])
            ->sortByDesc(fn($t) => [$t['year'], $t['semester_number']])
            ->values();
    }

    /**
     * Clone ข้อมูลจากเทอมที่เคยบันทึกไว้ — เลือกส่วนได้: schedules / opened / teachers
     * ไม่ทับข้อมูลที่มีอยู่แล้วในเทอมปลายทาง (firstOrCreate)
     */
    public function cloneFromTerm(int $fromYearId, int $fromSemesterId, int $toYearId, int $toSemesterId, array $parts): array
    {
        $result = ['schedules' => 0, 'grades' => 0, 'classrooms' => 0, 'courses' => 0, 'teacher_statuses' => 0, 'teacher_courses' => 0];

        DB::transaction(function () use ($fromYearId, $fromSemesterId, $toYearId, $toSemesterId, $parts, &$result) {

            if (in_array('schedules', $parts)) {
                $sources = YearlySchedule::where('academic_year_id', $fromYearId)->where('semester_id', $fromSemesterId)->get();
                foreach ($sources as $src) {
                    YearlySchedule::firstOrCreate(
                        [
                            'academic_year_id' => $toYearId,
                            'semester_id' => $toSemesterId,
                            'education_level_id' => $src->education_level_id,
                        ],
                        [
                            'teaching_days' => $src->teaching_days,
                            'start_time' => $src->start_time,
                            'period_duration' => $src->period_duration,
                            'day_configs' => $src->day_configs,
                        ]
                    )->wasRecentlyCreated && $result['schedules']++;
                }
            }

            if (in_array('opened', $parts)) {
                $grades = OpenedGrade::where('academic_year_id', $fromYearId)->where('semester_id', $fromSemesterId)->get();
                foreach ($grades as $src) {
                    OpenedGrade::firstOrCreate([
                        'academic_year_id' => $toYearId,
                        'semester_id' => $toSemesterId,
                        'grade_id' => $src->grade_id,
                    ])->wasRecentlyCreated && $result['grades']++;
                }

                $classrooms = OpenedClassroom::where('academic_year_id', $fromYearId)->where('semester_id', $fromSemesterId)->get();
                foreach ($classrooms as $src) {
                    OpenedClassroom::firstOrCreate([
                        'academic_year_id' => $toYearId,
                        'semester_id' => $toSemesterId,
                        'grade_id' => $src->grade_id,
                        'classroom_id' => $src->classroom_id,
                    ])->wasRecentlyCreated && $result['classrooms']++;
                }

                if ($fromSemesterId === $toSemesterId) {
                    // เทอมเดียวกัน (ต่างปี) — course id ชุดเดียวกัน copy ตรงได้
                    $courses = OpenedCourse::where('academic_year_id', $fromYearId)->where('semester_id', $fromSemesterId)->get();
                    foreach ($courses as $src) {
                        OpenedCourse::firstOrCreate([
                            'academic_year_id' => $toYearId,
                            'semester_id' => $toSemesterId,
                            'grade_id' => $src->grade_id,
                            'classroom_id' => $src->classroom_id,
                            'course_id' => $src->course_id,
                        ])->wasRecentlyCreated && $result['courses']++;
                    }
                } else {
                    // ข้ามเทอม — รายวิชาผูกกับเทอม ต้อง gen ใหม่จาก Course master ของเทอมปลายทาง
                    foreach ($classrooms as $src) {
                        $courseIds = Course::where('grade_id', $src->grade_id)
                            ->where('semester_id', $toSemesterId)
                            ->where('status', 1)
                            ->pluck('id');

                        foreach ($courseIds as $courseId) {
                            OpenedCourse::firstOrCreate([
                                'academic_year_id' => $toYearId,
                                'semester_id' => $toSemesterId,
                                'grade_id' => $src->grade_id,
                                'classroom_id' => $src->classroom_id,
                                'course_id' => $courseId,
                            ])->wasRecentlyCreated && $result['courses']++;
                        }
                    }
                }
            }

            if (in_array('teachers', $parts)) {
                $userId = Auth::id();
                $statuses = TeacherTermStatus::where('academic_year_id', $fromYearId)->where('semester_id', $fromSemesterId)->get();
                foreach ($statuses as $src) {
                    TeacherTermStatus::firstOrCreate(
                        [
                            'teacher_id' => $src->teacher_id,
                            'academic_year_id' => $toYearId,
                            'semester_id' => $toSemesterId,
                        ],
                        [
                            'status' => $src->status,
                            'can_be_scheduled' => $src->can_be_scheduled,
                            'max_periods_per_day' => $src->max_periods_per_day,
                            'max_periods_per_week' => $src->max_periods_per_week,
                            'unavailable_periods' => $src->unavailable_periods,
                            'notes' => $src->notes,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]
                    )->wasRecentlyCreated && $result['teacher_statuses']++;
                }

                if ($fromSemesterId === $toSemesterId) {
                    // วิชาที่ครูสอน (รายเทอม) — copy ได้เฉพาะเทอมเดียวกัน เพราะ course id ผูกกับเทอม
                    $termCourses = TeacherTermCourse::where('academic_year_id', $fromYearId)->where('semester_id', $fromSemesterId)->get();
                    foreach ($termCourses as $src) {
                        TeacherTermCourse::firstOrCreate([
                            'teacher_id' => $src->teacher_id,
                            'academic_year_id' => $toYearId,
                            'semester_id' => $toSemesterId,
                            'course_id' => $src->course_id,
                        ])->wasRecentlyCreated && $result['teacher_courses']++;
                    }
                }
            }
        });

        return $result;
    }
}
