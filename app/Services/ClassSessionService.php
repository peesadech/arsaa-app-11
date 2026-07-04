<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\ClassSessionStudent;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TimetableEntry;
use App\Models\TimetableSolution;
use App\Models\YearlySchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ClassSessionService
{
    /**
     * ตารางสอนของวันหนึ่ง (ตาม active timetable solution) พร้อม session ที่เปิดไว้แล้ว (ถ้ามี)
     * $teacherId = null → ดึงทุกครู (สำหรับ admin)
     */
    public function scheduleFor(int $yearId, int $semesterId, Carbon $date, ?int $teacherId = null): Collection
    {
        $solution = $this->activeSolution($yearId, $semesterId);
        if (!$solution) {
            return collect();
        }

        $day = $date->dayOfWeekIso; // 1=Mon .. 7=Sun (ตรงกับ TimetableEntry.day)

        $query = TimetableEntry::where('solution_id', $solution->id)
            ->where('day', $day)
            ->with([
                'openedCourse.course.subjectGroup',
                'openedCourse.grade.educationLevel',
                'openedCourse.classroom',
                'teacher',
                'room',
            ])
            ->orderBy('period');

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        $entries = $query->get();

        // เวลาเริ่ม/จบ ต่อคาบ (จาก YearlySchedule ตามระดับการศึกษา)
        $yearlySchedules = YearlySchedule::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->get()
            ->keyBy('education_level_id');

        // session ที่เปิดไว้แล้วของวันนี้ (map ด้วย timetable_entry_id)
        $sessions = ClassSession::whereDate('session_date', $date->toDateString())
            ->whereIn('timetable_entry_id', $entries->pluck('id'))
            ->get()
            ->keyBy('timetable_entry_id');

        return $entries->map(function (TimetableEntry $entry) use ($yearlySchedules, $sessions) {
            $oc = $entry->openedCourse;
            $eduId = $oc?->grade?->education_level_id;
            [$start, $end] = $this->periodTimes($yearlySchedules->get($eduId), $entry->day, $entry->period);

            $entry->computed_start = $start;
            $entry->computed_end = $end;
            $entry->existing_session = $sessions->get($entry->id);
            return $entry;
        });
    }

    /**
     * เปิด session ของ timetable entry ในวันหนึ่ง — ถ้ามีอยู่แล้วคืนอันเดิม (ไม่สร้างซ้ำ)
     */
    public function openOrCreate(TimetableEntry $entry, Carbon $date, ?int $actorUserId = null): ClassSession
    {
        $existing = ClassSession::where('timetable_entry_id', $entry->id)
            ->whereDate('session_date', $date->toDateString())
            ->first();

        if ($existing) {
            return $existing;
        }

        $oc = $entry->openedCourse;
        $eduId = $oc?->grade?->education_level_id;
        $yearly = $oc
            ? YearlySchedule::where('academic_year_id', $oc->academic_year_id)
                ->where('semester_id', $oc->semester_id)
                ->where('education_level_id', $eduId)
                ->first()
            : null;
        [$start, $end] = $this->periodTimes($yearly, $entry->day, $entry->period);

        return ClassSession::create([
            'academic_year_id'   => $oc?->academic_year_id,
            'semester_id'        => $oc?->semester_id,
            'timetable_entry_id' => $entry->id,
            'opened_course_id'   => $oc?->id,
            'course_id'          => $oc?->course_id,
            'grade_id'           => $oc?->grade_id,
            'classroom_id'       => $oc?->classroom_id,
            'teacher_id'         => $entry->teacher_id,
            'session_date'       => $date->toDateString(),
            'day'                => $entry->day,
            'period'             => $entry->period,
            'start_time'         => $start,
            'end_time'           => $end,
            'status'             => ClassSession::STATUS_OPEN,
            'created_by'         => $actorUserId,
        ]);
    }

    /**
     * นักเรียนในห้องของ session (เรียงตามชื่อ) + record attendance เดิม (ถ้ามี)
     */
    public function studentsFor(ClassSession $session): Collection
    {
        $enrollments = StudentEnrollment::where('academic_year_id', $session->academic_year_id)
            ->where('semester_id', $session->semester_id)
            ->where('grade_id', $session->grade_id)
            ->where('classroom_id', $session->classroom_id)
            ->where('status', StudentEnrollment::STATUS_ENROLLED)
            ->with('student')
            ->orderBy(Student::select('name_th')->whereColumn('students.id', 'student_enrollments.student_id'))
            ->get();

        return $enrollments->pluck('student')->filter()->values();
    }

    /**
     * บันทึกการเข้าเรียนทั้งห้อง
     * $rows: [student_id => ['attendance_status_id' => int|null, 'remark' => ?string, 'arrival_time' => ?string]]
     */
    public function saveAttendance(ClassSession $session, array $rows, ?int $actorUserId = null): int
    {
        $saved = 0;

        foreach ($rows as $studentId => $row) {
            $statusId = ($row['attendance_status_id'] ?? '') !== '' ? (int) $row['attendance_status_id'] : null;

            ClassSessionStudent::updateOrCreate(
                ['class_session_id' => $session->id, 'student_id' => $studentId],
                [
                    'attendance_status_id' => $statusId,
                    'arrival_time'         => $row['arrival_time'] ?? null,
                    'remark'               => $row['remark'] ?? null,
                    'created_by'           => $actorUserId,
                ]
            );
            $saved++;
        }

        return $saved;
    }

    /**
     * สรุปการเข้าเรียนของนักเรียนคนหนึ่ง แยกตามชนิดสถานะ (สำหรับหน้าประวัติ/รายงาน)
     * คืน ['present'=>, 'late'=>, 'leave'=>, 'absent'=>, 'total'=>, 'percent'=>]
     */
    public function summaryForStudent(int $studentId, array $sessionIds): array
    {
        $records = ClassSessionStudent::whereIn('class_session_id', $sessionIds)
            ->where('student_id', $studentId)
            ->with('attendanceStatus')
            ->get();

        $present = $late = $leave = $absent = 0;
        foreach ($records as $r) {
            $st = $r->attendanceStatus;
            if (!$st) continue;
            if ($st->is_count_as_present) $present++;
            if ($st->is_late) $late++;
            if ($st->is_leave) $leave++;
            if ($st->is_count_as_absent) $absent++;
        }
        $total = count($sessionIds);

        return [
            'present' => $present,
            'late'    => $late,
            'leave'   => $leave,
            'absent'  => $absent,
            'total'   => $total,
            'percent' => $total > 0 ? round($present / $total * 100, 1) : 0.0,
        ];
    }

    /**
     * สรุปการเข้าเรียนของนักเรียน แยกตามรายวิชา (สำหรับหน้าประวัตินักเรียน)
     * คืน collection ของ ['course'=>, 'sessions'=>, 'present'=>, 'late'=>, 'leave'=>, 'absent'=>, 'activity'=>, 'percent'=>]
     */
    public function summaryByCourseForStudent(int $studentId): Collection
    {
        $groups = ClassSessionStudent::where('student_id', $studentId)
            ->with(['attendanceStatus', 'classSession.course', 'classSession.academicYear', 'classSession.semester'])
            ->get()
            ->groupBy(fn ($r) => $r->classSession?->course_id);

        return $groups->map(function ($recs) {
            $t = ['sessions' => $recs->count(), 'present' => 0, 'late' => 0, 'leave' => 0, 'absent' => 0, 'activity' => 0];
            foreach ($recs as $r) {
                $st = $r->attendanceStatus;
                if (!$st) continue;
                if ($st->is_count_as_present) $t['present']++;
                if ($st->is_late)             $t['late']++;
                if ($st->is_leave)            $t['leave']++;
                if ($st->is_count_as_absent)  $t['absent']++;
                if (in_array($st->status_type, ['ACTIVITY', 'COMPETITION'], true)) $t['activity']++;
            }
            $t['percent'] = $t['sessions'] > 0 ? round($t['present'] / $t['sessions'] * 100, 1) : 0.0;
            $t['course'] = $recs->first()?->classSession?->course?->name ?? '—';
            return $t;
        })->values();
    }

    private function activeSolution(int $yearId, int $semesterId): ?TimetableSolution
    {
        return TimetableSolution::whereHas('generation', fn ($q) => $q
                ->where('academic_year_id', $yearId)
                ->where('semester_id', $semesterId)
                ->whereIn('status', ['completed', 'manual']))
            ->where('is_selected', true)
            ->first();
    }

    /**
     * คำนวณเวลาเริ่ม/จบของคาบ — break-aware (ตาม day_configs: per-day start_time + breaks[period])
     * mirror logic เดียวกับ manual-editor.blade calcTimes()
     * คืน [startHHMM|null, endHHMM|null]
     */
    private function periodTimes(?YearlySchedule $yearly, ?int $day, ?int $period): array
    {
        if (!$yearly || !$period) {
            return [null, null];
        }

        $configs = $yearly->day_configs;
        if (is_string($configs)) {
            $configs = json_decode($configs, true);
        }
        $configs = is_array($configs) ? $configs : [];
        $dc = $configs[$day] ?? $configs[(string) $day] ?? [];

        $startStr = ($dc['start_time'] ?? null) ?: $yearly->start_time;
        if (!$startStr) {
            return [null, null];
        }

        $duration = (int) ($yearly->period_duration ?: 50);
        $breaks = $dc['breaks'] ?? [];

        try {
            $min = $this->timeToMin(substr($startStr, 0, 5));
        } catch (\Throwable $e) {
            return [null, null];
        }

        for ($p = 1; $p <= $period; $p++) {
            if ($p === $period) {
                return [$this->minToTime($min), $this->minToTime($min + $duration)];
            }
            $min += $duration;
            if (is_array($breaks)) {
                $min += (int) ($breaks[$p] ?? $breaks[(string) $p] ?? 0);
            }
        }

        return [null, null];
    }

    private function timeToMin(string $t): int
    {
        $p = explode(':', $t);
        return ((int) $p[0]) * 60 + (int) ($p[1] ?? 0);
    }

    private function minToTime(int $m): string
    {
        return str_pad((string) intdiv($m, 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) ($m % 60), 2, '0', STR_PAD_LEFT);
    }
}
