<?php

namespace App\Services\Timetable;

use App\Models\TimetableEntry;

class ConstraintChecker
{
    public function __construct(private DataLoader $data)
    {
    }

    /**
     * ตรวจว่าสามารถวาง entry ใน slot นี้ได้หรือไม่
     * ใช้สำหรับทั้ง GA และ manual edit
     */
    public function canPlace(
        int $solutionId,
        int $openedCourseId,
        int $teacherId,
        int $roomId,
        int $day,
        int $period,
        ?int $excludeEntryId = null,
    ): ValidationResult {
        $violations = [];

        $oc = $this->data->getOpenedCourse($openedCourseId);
        if (!$oc) {
            $violations[] = new Violation('invalid_course', 'hard', 'ไม่พบวิชาที่เปิดสอน');
            return new ValidationResult(false, $violations);
        }

        $eduLevelId = $this->data->getEducationLevelForOpenedCourse($openedCourseId);

        // Hard: Teacher clash - ครูสอน 2 ห้องพร้อมกัน
        $teacherClash = TimetableEntry::where('solution_id', $solutionId)
            ->where('teacher_id', $teacherId)
            ->where('day', $day)
            ->where('period', $period)
            ->when($excludeEntryId, fn($q) => $q->where('id', '!=', $excludeEntryId))
            ->with('openedCourse.course', 'openedCourse.classroom')
            ->first();

        if ($teacherClash) {
            $clashCourse = $teacherClash->openedCourse->course->name ?? '?';
            $clashClass = $teacherClash->openedCourse->classroom->name ?? '?';
            $teacher = $this->data->getTeacher($teacherId);
            $violations[] = new Violation(
                'teacher_clash',
                'hard',
                "ครู{$teacher->name} สอนวิชา{$clashCourse} ห้อง {$clashClass} ในคาบนี้แล้ว"
            );
        }

        // Hard: Room clash - ห้องถูกใช้ 2 วิชาพร้อมกัน
        if ($roomId) {
            $roomClash = TimetableEntry::where('solution_id', $solutionId)
                ->where('room_id', $roomId)
                ->where('day', $day)
                ->where('period', $period)
                ->when($excludeEntryId, fn($q) => $q->where('id', '!=', $excludeEntryId))
                ->with('openedCourse.course', 'openedCourse.classroom')
                ->first();

            if ($roomClash) {
                $room = $this->data->getRoom($roomId);
                $clashCourse = $roomClash->openedCourse->course->name ?? '?';
                $clashClass = $roomClash->openedCourse->classroom->name ?? '?';
                $violations[] = new Violation(
                    'room_clash',
                    'hard',
                    "ห้อง {$room->room_number} ถูกใช้โดยวิชา{$clashCourse} ห้อง {$clashClass} ในคาบนี้แล้ว"
                );
            }
        }

        // Hard: Classroom clash - ห้องเรียนมี 2 วิชาพร้อมกัน
        $classroomClash = TimetableEntry::where('solution_id', $solutionId)
            ->where('day', $day)
            ->where('period', $period)
            ->when($excludeEntryId, fn($q) => $q->where('id', '!=', $excludeEntryId))
            ->whereHas('openedCourse', function ($q) use ($oc) {
                $q->where('classroom_id', $oc->classroom_id)
                    ->where('grade_id', $oc->grade_id);
            })
            ->with('openedCourse.course')
            ->first();

        if ($classroomClash) {
            $clashCourse = $classroomClash->openedCourse->course->name ?? '?';
            $violations[] = new Violation(
                'classroom_clash',
                'hard',
                "ห้องเรียน {$oc->classroom->name} มีวิชา{$clashCourse} ในคาบนี้แล้ว"
            );
        }

        // Hard: Teacher unavailable
        if ($eduLevelId && !$this->data->isTeacherAvailable($teacherId, $eduLevelId, $day, $period)) {
            $teacher = $this->data->getTeacher($teacherId);
            $violations[] = new Violation(
                'teacher_unavailable',
                'hard',
                "ครู{$teacher->name} ไม่ว่างในวัน{$this->dayName($day)} คาบที่ {$period}"
            );
        }

        // Hard: Room unavailable
        if ($roomId && $eduLevelId && !$this->data->isRoomAvailable($roomId, $eduLevelId, $day, $period)) {
            $room = $this->data->getRoom($roomId);
            $violations[] = new Violation(
                'room_unavailable',
                'hard',
                "ห้อง {$room->room_number} ไม่ว่างในวัน{$this->dayName($day)} คาบที่ {$period}"
            );
        }

        // Hard: Preferred days (วันที่สอนได้)
        $preferredDays = $oc->course->preferred_days ?? null;
        if (!empty($preferredDays) && !in_array($day, array_map('intval', $preferredDays))) {
            $preferredNames = collect($preferredDays)->map(fn($d) => $this->dayName($d))->join(', ');
            $violations[] = new Violation(
                'preferred_day',
                'hard',
                "วิชา{$oc->course->name} สอนได้เฉพาะวัน: {$preferredNames} (วัน{$this->dayName($day)} ไม่อยู่ในวันที่กำหนด)"
            );
        }

        // Hard: วิชาเดียวกันห้ามลงซ้ำในวันเดียว (ยกเว้นคาบติดกันของ session เดียวกัน)
        $periodsPerSession = $oc->course->periods_per_session ?? 1;
        $existingPeriodsOnDay = TimetableEntry::where('solution_id', $solutionId)
            ->where('opened_course_id', $openedCourseId)
            ->where('day', $day)
            ->when($excludeEntryId, fn($q) => $q->where('id', '!=', $excludeEntryId))
            ->pluck('period')
            ->sort()
            ->values()
            ->toArray();

        if (!empty($existingPeriodsOnDay)) {
            // ถ้ายังลงไม่ครบ session (เช่น session=2 ลงไปแล้ว 1 คาบ) — ต้องเป็นคาบติดกัน
            if (count($existingPeriodsOnDay) < $periodsPerSession) {
                $minExisting = min($existingPeriodsOnDay);
                $maxExisting = max($existingPeriodsOnDay);
                $isConsecutive = ($period === $minExisting - 1) || ($period === $maxExisting + 1);
                if (!$isConsecutive) {
                    $violations[] = new Violation(
                        'same_course_same_day',
                        'hard',
                        "วิชา{$oc->course->name} ต้องลงคาบติดกัน (คาบที่ " . implode(',', $existingPeriodsOnDay) . " อยู่แล้ว)"
                    );
                }
            } else {
                // ลงครบ session แล้ว — ห้ามลงซ้ำอีก
                $violations[] = new Violation(
                    'same_course_same_day',
                    'hard',
                    "วิชา{$oc->course->name} ถูกจัดในวัน{$this->dayName($day)} ครบแล้ว ({$periodsPerSession} คาบ/ครั้ง) ไม่สามารถลงซ้ำได้"
                );
            }
        }

        // Soft: Teacher max periods per day
        $termStatus = $this->data->getTeacherTermStatus($teacherId);
        if ($termStatus && $termStatus->max_periods_per_day) {
            $teacherPeriodsToday = TimetableEntry::where('solution_id', $solutionId)
                ->where('teacher_id', $teacherId)
                ->where('day', $day)
                ->when($excludeEntryId, fn($q) => $q->where('id', '!=', $excludeEntryId))
                ->count();
            if ($teacherPeriodsToday >= $termStatus->max_periods_per_day) {
                $teacher = $this->data->getTeacher($teacherId);
                $violations[] = new Violation(
                    'teacher_max_periods_day',
                    'soft',
                    "ครู{$teacher->name} ถึงขีดจำกัด {$termStatus->max_periods_per_day} คาบ/วัน แล้ว"
                );
            }
        }

        // Soft: Teacher max periods per week
        if ($termStatus && $termStatus->max_periods_per_week) {
            $teacherPeriodsWeek = TimetableEntry::where('solution_id', $solutionId)
                ->where('teacher_id', $teacherId)
                ->when($excludeEntryId, fn($q) => $q->where('id', '!=', $excludeEntryId))
                ->count();
            if ($teacherPeriodsWeek >= $termStatus->max_periods_per_week) {
                $teacher = $this->data->getTeacher($teacherId);
                $violations[] = new Violation(
                    'teacher_max_periods_week',
                    'soft',
                    "ครู{$teacher->name} ถึงขีดจำกัด {$termStatus->max_periods_per_week} คาบ/สัปดาห์ แล้ว"
                );
            }
        }

        $hasHard = collect($violations)->contains(fn($v) => $v->severity === 'hard');
        return new ValidationResult(!$hasHard, $violations);
    }

    /**
     * หา conflict ทั้งหมดของ solution (สำหรับ dashboard)
     */
    public function findAllConflicts(int $solutionId): array
    {
        $entries = TimetableEntry::where('solution_id', $solutionId)
            ->with('openedCourse.course', 'openedCourse.classroom', 'openedCourse.grade', 'teacher', 'room')
            ->get();

        $conflicts = [];

        // Group by day+period to check clashes
        $byTeacherSlot = [];
        $byRoomSlot = [];
        $byClassroomSlot = [];
        $coursePeriodCount = [];

        foreach ($entries as $entry) {
            $key = "{$entry->day}_{$entry->period}";

            // Teacher grouping
            if ($entry->teacher_id) {
                $tKey = "{$entry->teacher_id}_{$key}";
                $byTeacherSlot[$tKey][] = $entry;
            }

            // Room grouping
            if ($entry->room_id) {
                $rKey = "{$entry->room_id}_{$key}";
                $byRoomSlot[$rKey][] = $entry;
            }

            // Classroom grouping
            $classroomId = $entry->openedCourse->classroom_id ?? null;
            $gradeId = $entry->openedCourse->grade_id ?? null;
            if ($classroomId && $gradeId) {
                $cKey = "{$classroomId}_{$gradeId}_{$key}";
                $byClassroomSlot[$cKey][] = $entry;
            }

            // Count periods per opened_course
            $coursePeriodCount[$entry->opened_course_id] = ($coursePeriodCount[$entry->opened_course_id] ?? 0) + 1;

            // Check teacher unavailable
            if ($entry->teacher_id) {
                $eduLevelId = $this->data->getEducationLevelForOpenedCourse($entry->opened_course_id);
                if ($eduLevelId && !$this->data->isTeacherAvailable($entry->teacher_id, $eduLevelId, $entry->day, $entry->period)) {
                    $conflicts[] = [
                        'type' => 'teacher_unavailable',
                        'severity' => 'hard',
                        'day' => $entry->day,
                        'period' => $entry->period,
                        'details' => [
                            'teacher_id' => $entry->teacher_id,
                            'teacher_name' => $entry->teacher->name ?? '?',
                            'entry_id' => $entry->id,
                            'message' => "ครู{$entry->teacher->name} ไม่ว่างในคาบนี้",
                        ],
                    ];
                }
            }

            // Check room unavailable
            if ($entry->room_id) {
                $eduLevelId = $this->data->getEducationLevelForOpenedCourse($entry->opened_course_id);
                if ($eduLevelId && !$this->data->isRoomAvailable($entry->room_id, $eduLevelId, $entry->day, $entry->period)) {
                    $conflicts[] = [
                        'type' => 'room_unavailable',
                        'severity' => 'hard',
                        'day' => $entry->day,
                        'period' => $entry->period,
                        'details' => [
                            'room_id' => $entry->room_id,
                            'room_number' => $entry->room->room_number ?? '?',
                            'entry_id' => $entry->id,
                            'message' => "ห้อง {$entry->room->room_number} ไม่ว่างในคาบนี้",
                        ],
                    ];
                }
            }
        }

        // Teacher clashes
        foreach ($byTeacherSlot as $entries) {
            if (count($entries) > 1) {
                $ids = collect($entries)->pluck('id')->toArray();
                $conflicts[] = [
                    'type' => 'teacher_clash',
                    'severity' => 'hard',
                    'day' => $entries[0]->day,
                    'period' => $entries[0]->period,
                    'details' => [
                        'teacher_id' => $entries[0]->teacher_id,
                        'teacher_name' => $entries[0]->teacher->name ?? '?',
                        'entry_ids' => $ids,
                        'message' => "ครู{$entries[0]->teacher->name} ถูกจัดสอน " . count($entries) . " วิชาพร้อมกัน",
                    ],
                ];
            }
        }

        // Room clashes
        foreach ($byRoomSlot as $entries) {
            if (count($entries) > 1) {
                $ids = collect($entries)->pluck('id')->toArray();
                $conflicts[] = [
                    'type' => 'room_clash',
                    'severity' => 'hard',
                    'day' => $entries[0]->day,
                    'period' => $entries[0]->period,
                    'details' => [
                        'room_id' => $entries[0]->room_id,
                        'room_number' => $entries[0]->room->room_number ?? '?',
                        'entry_ids' => $ids,
                        'message' => "ห้อง {$entries[0]->room->room_number} ถูกใช้ " . count($entries) . " วิชาพร้อมกัน",
                    ],
                ];
            }
        }

        // Classroom clashes
        foreach ($byClassroomSlot as $entries) {
            if (count($entries) > 1) {
                $ids = collect($entries)->pluck('id')->toArray();
                $classroom = $entries[0]->openedCourse->classroom->name ?? '?';
                $conflicts[] = [
                    'type' => 'classroom_clash',
                    'severity' => 'hard',
                    'day' => $entries[0]->day,
                    'period' => $entries[0]->period,
                    'details' => [
                        'classroom' => $classroom,
                        'entry_ids' => $ids,
                        'message' => "ห้องเรียน {$classroom} มี " . count($entries) . " วิชาพร้อมกัน",
                    ],
                ];
            }
        }

        // Period count mismatch
        foreach ($coursePeriodCount as $ocId => $count) {
            $oc = $this->data->getOpenedCourse($ocId);
            if (!$oc) continue;
            $required = $oc->course->periods_per_week ?? 0;
            if ($count !== $required) {
                $conflicts[] = [
                    'type' => 'period_count_mismatch',
                    'severity' => 'hard',
                    'day' => null,
                    'period' => null,
                    'details' => [
                        'opened_course_id' => $ocId,
                        'course_name' => $oc->course->name ?? '?',
                        'classroom' => $oc->classroom->name ?? '?',
                        'required' => $required,
                        'actual' => $count,
                        'message' => "วิชา{$oc->course->name} ห้อง {$oc->classroom->name} จัด {$count}/{$required} คาบ",
                    ],
                ];
            }
        }

        return $conflicts;
    }

    private function dayName(int $day): string
    {
        return match ($day) {
            1 => 'จันทร์',
            2 => 'อังคาร',
            3 => 'พุธ',
            4 => 'พฤหัสบดี',
            5 => 'ศุกร์',
            6 => 'เสาร์',
            7 => 'อาทิตย์',
            default => "วันที่ {$day}",
        };
    }
}

class ValidationResult
{
    public function __construct(
        public readonly bool $valid,
        /** @var Violation[] */
        public readonly array $violations = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'violations' => array_map(fn($v) => $v->toArray(), $this->violations),
        ];
    }
}

class Violation
{
    public function __construct(
        public readonly string $type,
        public readonly string $severity,
        public readonly string $message,
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'severity' => $this->severity,
            'message' => $this->message,
        ];
    }
}
