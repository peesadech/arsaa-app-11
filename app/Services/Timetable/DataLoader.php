<?php

namespace App\Services\Timetable;

use App\Models\OpenedCourse;
use App\Models\YearlySchedule;
use App\Models\Teacher;
use App\Models\TeacherTermStatus;
use App\Models\Room;
use Illuminate\Support\Collection;

class DataLoader
{
    private Collection $openedCourses;
    private array $yearlySchedules = [];
    private array $teacherMap = [];
    private array $roomMap = [];
    private array $courseTeachers = [];
    private array $courseRooms = [];
    private array $validSlots = [];
    private array $teacherBlocked = [];
    private array $roomBlocked = [];
    private array $courseEducationLevel = [];
    private array $teacherTermStatuses = [];

    public function __construct(
        private int $academicYearId,
        private int $semesterId,
        private ?array $scope = null,
    ) {
        $this->load();
    }

    private function load(): void
    {
        $this->loadYearlySchedules();
        $this->loadOpenedCourses();
        $this->loadTeachers();
        $this->loadRooms();
        $this->buildValidSlots();
        $this->buildTeacherBlocked();
        $this->buildRoomBlocked();
    }

    private function loadYearlySchedules(): void
    {
        $schedules = YearlySchedule::where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->get();

        foreach ($schedules as $schedule) {
            $this->yearlySchedules[$schedule->education_level_id] = $schedule;
        }
    }

    private function loadOpenedCourses(): void
    {
        $query = OpenedCourse::with(['course.teachers', 'course.rooms', 'course.subjectGroup', 'grade', 'classroom'])
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId);

        if ($this->scope) {
            if (!empty($this->scope['grade_ids'])) {
                $query->whereIn('grade_id', $this->scope['grade_ids']);
            }
            if (!empty($this->scope['classroom_ids'])) {
                $query->whereIn('classroom_id', $this->scope['classroom_ids']);
            }
        }

        $this->openedCourses = $query->get();

        foreach ($this->openedCourses as $oc) {
            $educationLevelId = $oc->grade->education_level_id ?? null;
            $this->courseEducationLevel[$oc->id] = $educationLevelId;

            $this->courseTeachers[$oc->course_id] = $oc->course->teachers->pluck('id')->toArray();
            $this->courseRooms[$oc->course_id] = $oc->course->rooms->pluck('id')->toArray();
        }
    }

    private function loadTeachers(): void
    {
        $teacherIds = collect($this->courseTeachers)->flatten()->unique()->toArray();
        if (empty($teacherIds)) return;

        $teachers = Teacher::whereIn('id', $teacherIds)->get();

        // Load term statuses in bulk (single query)
        $termStatuses = TeacherTermStatus::whereIn('teacher_id', $teacherIds)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->get()
            ->keyBy('teacher_id');

        foreach ($teachers as $teacher) {
            // Skip globally inactive teachers
            if ($teacher->status !== 1) continue;

            // Skip teachers not schedulable for this term
            $termStatus = $termStatuses->get($teacher->id);
            if ($termStatus && !$termStatus->can_be_scheduled) continue;

            $this->teacherMap[$teacher->id] = $teacher;
            $this->teacherTermStatuses[$teacher->id] = $termStatus;
        }
    }

    private function loadRooms(): void
    {
        $roomIds = collect($this->courseRooms)->flatten()->unique()->toArray();
        if (empty($roomIds)) return;

        $rooms = Room::whereIn('id', $roomIds)->get();
        foreach ($rooms as $room) {
            $this->roomMap[$room->id] = $room;
        }
    }

    private function buildValidSlots(): void
    {
        foreach ($this->yearlySchedules as $eduLevelId => $schedule) {
            $slots = [];
            $teachingDays = $schedule->teaching_days ?? [];
            $dayConfigs = $schedule->day_configs ?? [];

            foreach ($teachingDays as $day) {
                $dayInt = (int) $day;
                $config = $dayConfigs[$day] ?? $dayConfigs[(string) $day] ?? null;
                if (!$config) continue;

                $periods = $config['periods'] ?? 0;
                for ($p = 1; $p <= $periods; $p++) {
                    $slots[] = ['day' => $dayInt, 'period' => $p];
                }
            }
            $this->validSlots[$eduLevelId] = $slots;
        }
    }

    private function buildTeacherBlocked(): void
    {
        foreach ($this->teacherMap as $teacherId => $teacher) {
            // Use term-specific unavailable_periods if exists, fallback to global
            $termStatus = $this->teacherTermStatuses[$teacherId] ?? null;
            $termUnavailable = $termStatus?->unavailable_periods ?? null;
            $unavailable = $termUnavailable ?? $teacher->unavailable_periods ?? [];

            if (!is_array($unavailable) || empty($unavailable)) continue;

            // Detect format: legacy = sequential array of objects with 'education_level_id' key
            // Term = nested object { eduLevelId: { day: [periods] } }
            $isLegacy = array_is_list($unavailable) && isset($unavailable[0]['education_level_id']);

            if ($isLegacy) {
                foreach ($unavailable as $entry) {
                    $eduLevelId = $entry['education_level_id'] ?? null;
                    $day = (int) ($entry['day'] ?? 0);
                    $startPeriod = (int) ($entry['start_period'] ?? 0);
                    $endPeriod = (int) ($entry['end_period'] ?? 0);

                    for ($p = $startPeriod; $p <= $endPeriod; $p++) {
                        $this->teacherBlocked[$teacherId][$eduLevelId][$day][$p] = true;
                    }
                }
            } else {
                // Term format: { eduLevelId: { day: [period, ...] } }
                foreach ($unavailable as $eduLevelId => $days) {
                    if (!is_array($days)) continue;
                    foreach ($days as $day => $periods) {
                        if (!is_array($periods)) continue;
                        foreach ($periods as $p) {
                            $this->teacherBlocked[$teacherId][(int)$eduLevelId][(int)$day][(int)$p] = true;
                        }
                    }
                }
            }
        }
    }

    private function buildRoomBlocked(): void
    {
        foreach ($this->roomMap as $roomId => $room) {
            $unavailable = $room->unavailable_periods ?? [];
            foreach ($unavailable as $entry) {
                $day = (int) ($entry['day'] ?? 0);
                $startTime = $entry['start'] ?? null;
                $endTime = $entry['end'] ?? null;
                if (!$startTime || !$endTime) continue;

                foreach ($this->yearlySchedules as $eduLevelId => $schedule) {
                    $periods = $this->timeToPeriods($schedule, $day, $startTime, $endTime);
                    foreach ($periods as $p) {
                        $this->roomBlocked[$roomId][$eduLevelId][$day][$p] = true;
                    }
                }
            }
        }
    }

    private function timeToPeriods(YearlySchedule $schedule, int $day, string $startTime, string $endTime): array
    {
        $dayConfigs = $schedule->day_configs ?? [];
        $dayStr = (string) $day;
        $config = $dayConfigs[$dayStr] ?? null;
        if (!$config) return [];

        $dayStartTime = $config['start_time'] ?? $schedule->start_time ?? '08:00';
        $periodDuration = $schedule->period_duration ?? 50;
        $breaks = $config['breaks'] ?? [];
        $totalPeriods = $config['periods'] ?? 0;

        $blockStart = $this->timeToMinutes($startTime);
        $blockEnd = $this->timeToMinutes($endTime);

        $currentMinute = $this->timeToMinutes($dayStartTime);
        $blocked = [];

        for ($p = 1; $p <= $totalPeriods; $p++) {
            $periodStart = $currentMinute;
            $periodEnd = $currentMinute + $periodDuration;

            if ($periodStart < $blockEnd && $periodEnd > $blockStart) {
                $blocked[] = $p;
            }

            $currentMinute = $periodEnd;
            if (isset($breaks[(string) $p])) {
                $currentMinute += (int) $breaks[(string) $p];
            }
        }

        return $blocked;
    }

    private function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);
        return ((int) $parts[0]) * 60 + ((int) ($parts[1] ?? 0));
    }

    // --- Public API ---

    public function getOpenedCourses(): Collection
    {
        return $this->openedCourses;
    }

    public function getYearlySchedule(int $educationLevelId): ?YearlySchedule
    {
        return $this->yearlySchedules[$educationLevelId] ?? null;
    }

    public function getYearlySchedules(): array
    {
        return $this->yearlySchedules;
    }

    public function getTeachersForCourse(int $courseId): array
    {
        $allTeachers = $this->courseTeachers[$courseId] ?? [];
        return array_values(array_filter($allTeachers, fn($id) => isset($this->teacherMap[$id])));
    }

    public function getTeacherTermStatus(int $teacherId): ?TeacherTermStatus
    {
        return $this->teacherTermStatuses[$teacherId] ?? null;
    }

    public function getRoomsForCourse(int $courseId): array
    {
        return $this->courseRooms[$courseId] ?? [];
    }

    public function getEducationLevelForOpenedCourse(int $openedCourseId): ?int
    {
        return $this->courseEducationLevel[$openedCourseId] ?? null;
    }

    public function getValidSlots(int $educationLevelId): array
    {
        return $this->validSlots[$educationLevelId] ?? [];
    }

    public function isTeacherAvailable(int $teacherId, int $educationLevelId, int $day, int $period): bool
    {
        return !isset($this->teacherBlocked[$teacherId][$educationLevelId][$day][$period]);
    }

    public function isRoomAvailable(int $roomId, int $educationLevelId, int $day, int $period): bool
    {
        return !isset($this->roomBlocked[$roomId][$educationLevelId][$day][$period]);
    }

    public function getTeacher(int $teacherId): ?Teacher
    {
        return $this->teacherMap[$teacherId] ?? null;
    }

    public function getRoom(int $roomId): ?Room
    {
        return $this->roomMap[$roomId] ?? null;
    }

    public function getOpenedCourse(int $openedCourseId): ?OpenedCourse
    {
        return $this->openedCourses->firstWhere('id', $openedCourseId);
    }

    public function getPeriodTime(int $educationLevelId, int $day, int $period): ?array
    {
        $schedule = $this->yearlySchedules[$educationLevelId] ?? null;
        if (!$schedule) return null;

        $dayConfigs = $schedule->day_configs ?? [];
        $config = $dayConfigs[(string) $day] ?? null;
        if (!$config) return null;

        $dayStartTime = $config['start_time'] ?? $schedule->start_time ?? '08:00';
        $periodDuration = $schedule->period_duration ?? 50;
        $breaks = $config['breaks'] ?? [];

        $currentMinute = $this->timeToMinutes($dayStartTime);

        for ($p = 1; $p <= $period; $p++) {
            if ($p === $period) {
                return [
                    'start' => $this->minutesToTime($currentMinute),
                    'end' => $this->minutesToTime($currentMinute + $periodDuration),
                ];
            }
            $currentMinute += $periodDuration;
            if (isset($breaks[(string) $p])) {
                $currentMinute += (int) $breaks[(string) $p];
            }
        }

        return null;
    }

    private function minutesToTime(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
