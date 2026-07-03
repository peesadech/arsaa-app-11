<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\TeacherSubstitution;
use App\Models\TeacherTermCourse;
use App\Models\TeacherTermStatus;
use App\Models\TimetableEntry;
use App\Models\TimetableSolution;
use App\Services\Timetable\ConstraintChecker;
use App\Services\Timetable\DataLoader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherSubstitutionService
{
    public function getActiveSolution(int $yearId, int $semesterId): ?TimetableSolution
    {
        return TimetableSolution::whereHas('generation', fn($q) => $q
                ->where('academic_year_id', $yearId)
                ->where('semester_id', $semesterId)
                ->whereIn('status', ['completed', 'manual']))
            ->where('is_selected', true)
            ->first();
    }

    public function getAffectedEntries(int $solutionId, int $teacherId): Collection
    {
        return TimetableEntry::where('solution_id', $solutionId)
            ->where('teacher_id', $teacherId)
            ->with('openedCourse.course.subjectGroup', 'openedCourse.classroom', 'openedCourse.grade', 'room')
            ->orderBy('day')
            ->orderBy('period')
            ->get();
    }

    /**
     * หาครูที่สอนแทนได้ ต่อ entry — คืน array keyed by entry id
     * แต่ละ candidate: teacher_id, name, valid (ไม่มี hard violation), violations (ข้อความ)
     */
    public function buildCandidates(Collection $entries, int $yearId, int $semesterId, int $excludeTeacherId): array
    {
        if ($entries->isEmpty()) return [];

        $courseIds = $entries->pluck('openedCourse.course_id')->unique()->values();

        // ครูที่สอนวิชานั้นได้: จาก pivot course_teacher (global) + teacher_term_courses (รายเทอม)
        $globalTeachers = DB::table('course_teacher')
            ->whereIn('course_id', $courseIds)
            ->get(['course_id', 'teacher_id']);

        $termTeachers = TeacherTermCourse::whereIn('course_id', $courseIds)
            ->where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->get(['course_id', 'teacher_id']);

        $courseTeacherMap = [];
        foreach ($globalTeachers as $row) {
            $courseTeacherMap[$row->course_id][$row->teacher_id] = true;
        }
        foreach ($termTeachers as $row) {
            $courseTeacherMap[$row->course_id][$row->teacher_id] = true;
        }

        $allCandidateIds = collect($courseTeacherMap)
            ->flatMap(fn($teachers) => array_keys($teachers))
            ->unique()
            ->reject(fn($id) => $id == $excludeTeacherId)
            ->values();

        if ($allCandidateIds->isEmpty()) return [];

        // เฉพาะครูที่ยัง active และจัดตารางได้ในเทอมนี้
        $nonSchedulableIds = TeacherTermStatus::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('can_be_scheduled', false)
            ->pluck('teacher_id');

        $teachers = Teacher::whereIn('id', $allCandidateIds)
            ->where('status', 1)
            ->whereNotIn('id', $nonSchedulableIds)
            ->get()
            ->keyBy('id');

        $dataLoader = new DataLoader($yearId, $semesterId);
        $checker = new ConstraintChecker($dataLoader);

        $result = [];
        foreach ($entries as $entry) {
            $courseId = $entry->openedCourse->course_id;
            $candidateIds = array_keys($courseTeacherMap[$courseId] ?? []);
            $candidates = [];

            foreach ($candidateIds as $candidateId) {
                if ($candidateId == $excludeTeacherId) continue;
                $teacher = $teachers->get($candidateId);
                if (!$teacher) continue;

                $check = $checker->canPlace(
                    $entry->solution_id,
                    $entry->opened_course_id,
                    $candidateId,
                    $entry->room_id ?? 0,
                    $entry->day,
                    $entry->period,
                    $entry->id,
                );

                $candidates[] = [
                    'teacher_id' => $candidateId,
                    'name' => $teacher->name,
                    'valid' => $check->valid,
                    'violations' => array_map(fn($v) => $v['message'], $check->toArray()['violations']),
                ];
            }

            // เรียง: สอนแทนได้ก่อน แล้วตามชื่อ
            usort($candidates, fn($a, $b) => [$a['valid'] ? 0 : 1, $a['name']] <=> [$b['valid'] ? 0 : 1, $b['name']]);

            $result[$entry->id] = $candidates;
        }

        return $result;
    }

    /**
     * ใช้การสอนแทน/ยกเลิกคาบ กับ entries ที่เลือก
     * $items: [['entry_id' => int, 'action' => 'substitute'|'unassign', 'to_teacher_id' => ?int]]
     * คืน ['applied' => int, 'errors' => [entry_id => message]]
     */
    public function apply(TimetableSolution $solution, Teacher $fromTeacher, array $items, ?string $reason = null): array
    {
        $entryIds = collect($items)->pluck('entry_id');
        $entries = TimetableEntry::where('solution_id', $solution->id)
            ->where('teacher_id', $fromTeacher->id)
            ->whereIn('id', $entryIds)
            ->with('openedCourse.course')
            ->get()
            ->keyBy('id');

        $generation = $solution->generation;
        $dataLoader = new DataLoader($generation->academic_year_id, $generation->semester_id);
        $checker = new ConstraintChecker($dataLoader);

        $applied = 0;
        $errors = [];

        foreach ($items as $item) {
            $entry = $entries->get($item['entry_id']);
            if (!$entry) {
                $errors[$item['entry_id']] = __('Entry not found or already reassigned');
                continue;
            }

            $action = $item['action'];
            $toTeacherId = $action === TeacherSubstitution::ACTION_SUBSTITUTE ? ($item['to_teacher_id'] ?? null) : null;

            if ($action === TeacherSubstitution::ACTION_SUBSTITUTE) {
                if (!$toTeacherId) {
                    $errors[$entry->id] = __('Please select a substitute teacher');
                    continue;
                }

                $check = $checker->canPlace(
                    $entry->solution_id,
                    $entry->opened_course_id,
                    $toTeacherId,
                    $entry->room_id ?? 0,
                    $entry->day,
                    $entry->period,
                    $entry->id,
                );

                if (!$check->valid) {
                    $hardMessages = collect($check->toArray()['violations'])
                        ->where('severity', 'hard')
                        ->pluck('message')
                        ->join(', ');
                    $errors[$entry->id] = $hardMessages;
                    continue;
                }
            }

            DB::transaction(function () use ($entry, $solution, $fromTeacher, $toTeacherId, $action, $reason, &$applied) {
                $entry->update(['teacher_id' => $toTeacherId]);

                TeacherSubstitution::create([
                    'solution_id' => $solution->id,
                    'timetable_entry_id' => $entry->id,
                    'opened_course_id' => $entry->opened_course_id,
                    'from_teacher_id' => $fromTeacher->id,
                    'to_teacher_id' => $toTeacherId,
                    'action' => $action,
                    'day' => $entry->day,
                    'period' => $entry->period,
                    'reason' => $reason,
                    'created_by' => Auth::id(),
                ]);

                $applied++;
            });
        }

        return ['applied' => $applied, 'errors' => $errors];
    }

    public function getHistory(int $solutionId, int $teacherId): Collection
    {
        return TeacherSubstitution::where('solution_id', $solutionId)
            ->where(fn($q) => $q->where('from_teacher_id', $teacherId)->orWhere('to_teacher_id', $teacherId))
            ->with('fromTeacher', 'toTeacher', 'openedCourse.course', 'openedCourse.classroom', 'createdBy')
            ->orderByDesc('created_at')
            ->get();
    }
}
