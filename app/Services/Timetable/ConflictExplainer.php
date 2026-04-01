<?php

namespace App\Services\Timetable;

use App\Models\TimetableEntry;

class ConflictExplainer
{
    private const DAY_NAMES = [
        1 => 'จันทร์', 2 => 'อังคาร', 3 => 'พุธ',
        4 => 'พฤหัสบดี', 5 => 'ศุกร์', 6 => 'เสาร์', 7 => 'อาทิตย์',
    ];

    public function __construct(private DataLoader $data)
    {
    }

    /**
     * อธิบายว่าทำไมวิชานี้ไม่สามารถวางใน slot นี้ได้
     * @return array<string> รายการเหตุผล
     */
    public function explain(int $solutionId, int $openedCourseId, int $day, int $period): array
    {
        $reasons = [];
        $oc = $this->data->getOpenedCourse($openedCourseId);
        if (!$oc) {
            return ['ไม่พบข้อมูลวิชาที่เปิดสอน'];
        }

        $eduLevel = $this->data->getEducationLevelForOpenedCourse($openedCourseId);
        $dayName = self::DAY_NAMES[$day] ?? "วันที่ {$day}";

        // Check classroom clash
        $classroomClash = TimetableEntry::where('solution_id', $solutionId)
            ->where('day', $day)
            ->where('period', $period)
            ->whereHas('openedCourse', fn($q) => $q->where('classroom_id', $oc->classroom_id)->where('grade_id', $oc->grade_id))
            ->with('openedCourse.course')
            ->first();

        if ($classroomClash) {
            $reasons[] = "ห้อง {$oc->classroom->name} มีวิชา{$classroomClash->openedCourse->course->name} อยู่แล้วในวัน{$dayName} คาบที่ {$period}";
        }

        // Check each eligible teacher
        $teachers = $this->data->getTeachersForCourse($oc->course_id);
        foreach ($teachers as $teacherId) {
            $teacher = $this->data->getTeacher($teacherId);
            if (!$teacher) continue;

            // Teacher unavailable
            if ($eduLevel && !$this->data->isTeacherAvailable($teacherId, $eduLevel, $day, $period)) {
                $reasons[] = "ครู{$teacher->name} ไม่ว่างในวัน{$dayName} คาบที่ {$period} (ตั้งค่าไม่ว่าง)";
            }

            // Teacher clash with other class
            $teacherClash = TimetableEntry::where('solution_id', $solutionId)
                ->where('teacher_id', $teacherId)
                ->where('day', $day)
                ->where('period', $period)
                ->with('openedCourse.course', 'openedCourse.classroom')
                ->first();

            if ($teacherClash) {
                $clashCourse = $teacherClash->openedCourse->course->name ?? '?';
                $clashClass = $teacherClash->openedCourse->classroom->name ?? '?';
                $reasons[] = "ครู{$teacher->name} สอนวิชา{$clashCourse} ห้อง {$clashClass} ในวัน{$dayName} คาบที่ {$period} อยู่แล้ว";
            }
        }

        // Check each eligible room
        $rooms = $this->data->getRoomsForCourse($oc->course_id);
        foreach ($rooms as $roomId) {
            $room = $this->data->getRoom($roomId);
            if (!$room) continue;

            // Room unavailable
            if ($eduLevel && !$this->data->isRoomAvailable($roomId, $eduLevel, $day, $period)) {
                $reasons[] = "ห้อง {$room->room_number} ไม่ว่างในวัน{$dayName} คาบที่ {$period} (ตั้งค่าไม่ว่าง)";
            }

            // Room clash
            $roomClash = TimetableEntry::where('solution_id', $solutionId)
                ->where('room_id', $roomId)
                ->where('day', $day)
                ->where('period', $period)
                ->with('openedCourse.course', 'openedCourse.classroom')
                ->first();

            if ($roomClash) {
                $clashCourse = $roomClash->openedCourse->course->name ?? '?';
                $clashClass = $roomClash->openedCourse->classroom->name ?? '?';
                $reasons[] = "ห้อง {$room->room_number} ถูกใช้โดยวิชา{$clashCourse} ห้อง {$clashClass} ในวัน{$dayName} คาบที่ {$period}";
            }
        }

        if (empty($reasons)) {
            $reasons[] = "สามารถวางวิชานี้ในคาบนี้ได้";
        }

        return $reasons;
    }
}
