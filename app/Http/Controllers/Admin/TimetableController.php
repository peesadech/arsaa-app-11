<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunTimetableGeneration;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\OpenedCourse;
use App\Models\Room;
use App\Models\Teacher;
use App\Models\TeacherTermCourse;
use App\Models\TimetableConflict;
use App\Models\TimetableEntry;
use App\Models\TimetableGeneration;
use App\Models\TimetableSolution;
use App\Models\YearlySchedule;
use App\Services\Timetable\ConflictExplainer;
use App\Services\Timetable\ConstraintChecker;
use App\Services\Timetable\DataLoader;
use App\Services\Timetable\GeneticAlgorithm\FitnessCalculator;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    private function resolveYearSemester(Request $request): array
    {
        $yearId = $request->session()->get('current_academic_year_id');
        $semesterId = $request->session()->get('current_semester_id');
        return [$yearId, $semesterId];
    }

    // ==================== PAGE ROUTES ====================

    public function index(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $generations = TimetableGeneration::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->with('user', 'solutions')
            ->orderByDesc('created_at')
            ->get();

        // Find active (selected) solution (from completed GA or manual)
        $activeSolution = TimetableSolution::whereHas('generation', fn($q) => $q->where('academic_year_id', $yearId)->where('semester_id', $semesterId)->whereIn('status', ['completed', 'manual']))
            ->where('is_selected', true)
            ->with('conflicts')
            ->first();

        return view('admin.timetable.index', compact('generations', 'activeSolution', 'yearId', 'semesterId'));
    }

    public function generateForm(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $grades = Grade::where('status', 1)->with('educationLevel')->get();
        $classrooms = Classroom::where('status', 1)->get();

        return view('admin.timetable.generate', compact('yearId', 'semesterId', 'grades', 'classrooms'));
    }

    public function generateStore(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $data = $request->validate([
            'population_size' => 'nullable|integer|min:10|max:100',
            'max_generations' => 'nullable|integer|min:50|max:2000',
            'solutions_requested' => 'nullable|integer|min:1|max:10',
            'scope_grade_ids' => 'nullable|array',
            'scope_classroom_ids' => 'nullable|array',
        ]);

        $scope = null;
        if (!empty($data['scope_grade_ids']) || !empty($data['scope_classroom_ids'])) {
            $scope = [
                'grade_ids' => $data['scope_grade_ids'] ?? [],
                'classroom_ids' => $data['scope_classroom_ids'] ?? [],
            ];
        }

        $generation = TimetableGeneration::create([
            'academic_year_id' => $yearId,
            'semester_id' => $semesterId,
            'user_id' => auth()->id(),
            'status' => 'pending',
            'population_size' => $data['population_size'] ?? 30,
            'max_generations' => $data['max_generations'] ?? 500,
            'solutions_requested' => $data['solutions_requested'] ?? 3,
            'scope' => $scope,
            'config' => [
                'crossover_rate' => 0.8,
                'mutation_rate' => 0.05,
                'tournament_size' => 3,
            ],
        ]);

        RunTimetableGeneration::dispatch($generation->id);

        return redirect()->route('admin.timetable.generations.show', $generation->id)
            ->with('status', 'เริ่ม Generate ตารางเรียนแล้ว กรุณารอสักครู่...');
    }

    public function showGeneration($id)
    {
        $generation = TimetableGeneration::with('solutions.conflicts', 'academicYear', 'semester', 'user')
            ->findOrFail($id);

        return view('admin.timetable.generation-show', compact('generation'));
    }

    public function showSolution($id, Request $request)
    {
        $solution = TimetableSolution::with('generation.academicYear', 'generation.semester')
            ->findOrFail($id);

        [$yearId, $semesterId] = [$solution->generation->academic_year_id, $solution->generation->semester_id];

        $yearlySchedules = YearlySchedule::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->with('educationLevel')
            ->get()
            ->keyBy('education_level_id');

        $grades = Grade::whereHas('openedCourses', fn($q) => $q->where('academic_year_id', $yearId)->where('semester_id', $semesterId))
            ->with('educationLevel')
            ->get();

        // Get classrooms that have opened courses
        $openedCourses = OpenedCourse::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->with('classroom', 'grade', 'course.subjectGroup')
            ->get();

        $classrooms = $openedCourses->groupBy(fn($oc) => "{$oc->grade_id}_{$oc->classroom_id}");

        $teachers = Teacher::whereHas('timetableEntries', fn($q) => $q->where('solution_id', $id))->get();
        $rooms = Room::whereHas('timetableEntries', fn($q) => $q->where('solution_id', $id))->with('building')->get();

        // Pre-transform for JSON (avoid Blade closure parsing issues)
        $openedCoursesJson = $openedCourses->map(function ($oc) {
            return [
                'id' => $oc->id,
                'course_name' => $oc->course->name,
                'subject_group' => $oc->course->subjectGroup->name_th ?? '',
                'subject_group_id' => $oc->course->subject_group_id,
                'classroom_id' => $oc->classroom_id,
                'classroom_name' => $oc->classroom->name,
                'grade_id' => $oc->grade_id,
                'grade_name' => $oc->grade->name_th ?? '',
                'education_level_id' => $oc->grade->education_level_id ?? null,
            ];
        })->values();

        $classroomsJson = $openedCourses->groupBy(function ($oc) {
            return $oc->grade_id . '_' . $oc->classroom_id;
        })->map(function ($group) {
            $first = $group->first();
            return [
                'grade_id' => $first->grade_id,
                'classroom_id' => $first->classroom_id,
                'label' => ($first->grade->name_th ?? '') . ' / ' . ($first->classroom->name ?? ''),
                'education_level_id' => $first->grade->education_level_id ?? null,
            ];
        })->values();

        $teachersJson = $teachers->map(function ($t) {
            return ['id' => $t->id, 'name' => $t->name];
        })->values();

        $roomsJson = $rooms->map(function ($r) {
            return ['id' => $r->id, 'label' => $r->room_number . ($r->building ? ' (' . $r->building->name_th . ')' : '')];
        })->values();

        return view('admin.timetable.solution-show', compact(
            'solution', 'yearlySchedules', 'grades', 'classrooms', 'openedCourses',
            'teachers', 'rooms', 'openedCoursesJson', 'classroomsJson', 'teachersJson', 'roomsJson'
        ));
    }

    public function viewByClassroom($classroomId, Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);
        $gradeId = $request->query('grade_id');

        $activeSolution = $this->getActiveSolution($yearId, $semesterId);
        if (!$activeSolution) {
            return redirect()->route('admin.timetable.index')->with('error', 'ยังไม่มีตารางเรียนที่ใช้งาน');
        }

        $classroom = Classroom::findOrFail($classroomId);
        $grade = Grade::with('educationLevel')->findOrFail($gradeId);

        $schedule = YearlySchedule::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('education_level_id', $grade->education_level_id)
            ->first();

        $entries = TimetableEntry::where('solution_id', $activeSolution->id)
            ->whereHas('openedCourse', fn($q) => $q->where('classroom_id', $classroomId)->where('grade_id', $gradeId))
            ->with('openedCourse.course.subjectGroup', 'teacher', 'room')
            ->get();

        return view('admin.timetable.grid-classroom', compact('classroom', 'grade', 'schedule', 'entries', 'activeSolution'));
    }

    public function viewByTeacher($teacherId, Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $activeSolution = $this->getActiveSolution($yearId, $semesterId);
        if (!$activeSolution) {
            return redirect()->route('admin.timetable.index')->with('error', 'ยังไม่มีตารางเรียนที่ใช้งาน');
        }

        $teacher = Teacher::findOrFail($teacherId);

        $entries = TimetableEntry::where('solution_id', $activeSolution->id)
            ->where('teacher_id', $teacherId)
            ->with('openedCourse.course.subjectGroup', 'openedCourse.classroom', 'openedCourse.grade', 'room')
            ->get();

        $yearlySchedules = YearlySchedule::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->with('educationLevel')
            ->get();

        return view('admin.timetable.grid-teacher', compact('teacher', 'entries', 'yearlySchedules', 'activeSolution'));
    }

    public function viewByRoom($roomId, Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $activeSolution = $this->getActiveSolution($yearId, $semesterId);
        if (!$activeSolution) {
            return redirect()->route('admin.timetable.index')->with('error', 'ยังไม่มีตารางเรียนที่ใช้งาน');
        }

        $room = Room::with('building')->findOrFail($roomId);

        $entries = TimetableEntry::where('solution_id', $activeSolution->id)
            ->where('room_id', $roomId)
            ->with('openedCourse.course.subjectGroup', 'openedCourse.classroom', 'openedCourse.grade', 'teacher')
            ->get();

        $yearlySchedules = YearlySchedule::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->with('educationLevel')
            ->get();

        return view('admin.timetable.grid-room', compact('room', 'entries', 'yearlySchedules', 'activeSolution'));
    }

    public function conflicts($solutionId)
    {
        $solution = TimetableSolution::with('generation.academicYear', 'generation.semester')->findOrFail($solutionId);
        $conflicts = TimetableConflict::where('solution_id', $solutionId)
            ->orderBy('severity')
            ->orderBy('type')
            ->get();

        return view('admin.timetable.conflicts', compact('solution', 'conflicts'));
    }

    // ==================== API ROUTES (JSON) ====================

    public function apiProgress($id)
    {
        $generation = TimetableGeneration::findOrFail($id);
        $bestFitness = $generation->config['best_fitness'] ?? null;

        return response()->json([
            'status' => $generation->status,
            'current_generation' => $generation->current_generation,
            'max_generations' => $generation->max_generations,
            'progress_percent' => $generation->max_generations > 0
                ? round(($generation->current_generation / $generation->max_generations) * 100)
                : 0,
            'best_fitness' => $bestFitness,
            'error_message' => $generation->error_message,
        ]);
    }

    public function apiSelect($id)
    {
        $solution = TimetableSolution::findOrFail($id);

        // Deselect all other solutions in same generation
        TimetableSolution::where('generation_id', $solution->generation_id)
            ->update(['is_selected' => false]);

        // Also deselect from other generations for same year/semester
        $gen = $solution->generation;
        $otherGenIds = TimetableGeneration::where('academic_year_id', $gen->academic_year_id)
            ->where('semester_id', $gen->semester_id)
            ->pluck('id');
        TimetableSolution::whereIn('generation_id', $otherGenIds)
            ->update(['is_selected' => false]);

        $solution->update(['is_selected' => true]);

        return response()->json(['success' => true]);
    }

    public function apiEntries($solutionId)
    {
        $entries = TimetableEntry::where('solution_id', $solutionId)
            ->with('openedCourse.course.subjectGroup', 'openedCourse.classroom', 'openedCourse.grade', 'teacher', 'room')
            ->get();

        return response()->json($entries->map(fn($e) => [
            'id' => $e->id,
            'opened_course_id' => $e->opened_course_id,
            'course_name' => $e->openedCourse->course->name ?? '',
            'subject_group' => $e->openedCourse->course->subjectGroup->name_th ?? '',
            'subject_group_id' => $e->openedCourse->course->subject_group_id,
            'classroom' => $e->openedCourse->classroom->name ?? '',
            'classroom_id' => $e->openedCourse->classroom_id,
            'grade_id' => $e->openedCourse->grade_id,
            'grade_name' => $e->openedCourse->grade->name_th ?? '',
            'teacher_name' => $e->teacher->name ?? '',
            'teacher_id' => $e->teacher_id,
            'room_number' => $e->room->room_number ?? '',
            'room_id' => $e->room_id,
            'day' => $e->day,
            'period' => $e->period,
            'is_locked' => $e->is_locked,
        ]));
    }

    public function apiMove(Request $request, $id)
    {
        $entry = TimetableEntry::with('openedCourse')->findOrFail($id);

        if ($entry->is_locked) {
            return response()->json(['success' => false, 'message' => 'คาบนี้ถูกล็อคอยู่ ไม่สามารถย้ายได้'], 422);
        }

        $data = $request->validate([
            'day' => 'required|integer|min:1|max:7',
            'period' => 'required|integer|min:1|max:20',
            'teacher_id' => 'nullable|integer',
            'room_id' => 'nullable|integer',
        ]);

        // Check conflicts using ConstraintChecker
        $dataLoader = new DataLoader(
            $entry->openedCourse->academic_year_id,
            $entry->openedCourse->semester_id,
        );
        $checker = new ConstraintChecker($dataLoader);

        $result = $checker->canPlace(
            $entry->solution_id,
            $entry->opened_course_id,
            $data['teacher_id'] ?? $entry->teacher_id,
            $data['room_id'] ?? $entry->room_id,
            $data['day'],
            $data['period'],
            $entry->id,
        );

        if (!$result->valid) {
            return response()->json([
                'success' => false,
                'violations' => $result->toArray()['violations'],
            ], 422);
        }

        $entry->update([
            'day' => $data['day'],
            'period' => $data['period'],
            'teacher_id' => $data['teacher_id'] ?? $entry->teacher_id,
            'room_id' => $data['room_id'] ?? $entry->room_id,
        ]);

        return response()->json(['success' => true]);
    }

    public function apiLock($id)
    {
        $entry = TimetableEntry::findOrFail($id);
        $entry->update(['is_locked' => !$entry->is_locked]);

        return response()->json(['success' => true, 'is_locked' => $entry->is_locked]);
    }

    public function apiDelete($id)
    {
        $entry = TimetableEntry::findOrFail($id);
        if ($entry->is_locked) {
            return response()->json(['success' => false, 'message' => 'คาบนี้ถูกล็อคอยู่'], 422);
        }
        $entry->delete();

        return response()->json(['success' => true]);
    }

    public function apiCreate(Request $request)
    {
        $data = $request->validate([
            'solution_id' => 'required|integer|exists:timetable_solutions,id',
            'opened_course_id' => 'required|integer|exists:opened_courses,id',
            'teacher_id' => 'required|integer|exists:teachers,id',
            'room_id' => 'nullable|integer|exists:rooms,id',
            'day' => 'required|integer|min:1|max:7',
            'period' => 'required|integer|min:1|max:20',
        ]);

        $oc = OpenedCourse::findOrFail($data['opened_course_id']);
        $dataLoader = new DataLoader($oc->academic_year_id, $oc->semester_id);
        $checker = new ConstraintChecker($dataLoader);

        $result = $checker->canPlace(
            $data['solution_id'],
            $data['opened_course_id'],
            $data['teacher_id'],
            $data['room_id'] ?? 0,
            $data['day'],
            $data['period'],
        );

        if (!$result->valid) {
            return response()->json([
                'success' => false,
                'violations' => $result->toArray()['violations'],
            ], 422);
        }

        $entry = TimetableEntry::create($data);

        return response()->json(['success' => true, 'entry_id' => $entry->id]);
    }

    public function apiCheckConflicts(Request $request)
    {
        $data = $request->validate([
            'solution_id' => 'required|integer',
            'opened_course_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'room_id' => 'nullable|integer',
            'day' => 'required|integer',
            'period' => 'required|integer',
            'exclude_entry_id' => 'nullable|integer',
        ]);

        $oc = OpenedCourse::findOrFail($data['opened_course_id']);
        $dataLoader = new DataLoader($oc->academic_year_id, $oc->semester_id);
        $checker = new ConstraintChecker($dataLoader);

        $result = $checker->canPlace(
            $data['solution_id'],
            $data['opened_course_id'],
            $data['teacher_id'],
            $data['room_id'] ?? 0,
            $data['day'],
            $data['period'],
            $data['exclude_entry_id'] ?? null,
        );

        return response()->json($result->toArray());
    }

    public function apiExplainSlot(Request $request)
    {
        $data = $request->validate([
            'solution_id' => 'required|integer',
            'opened_course_id' => 'required|integer',
            'day' => 'required|integer',
            'period' => 'required|integer',
        ]);

        $oc = OpenedCourse::findOrFail($data['opened_course_id']);
        $dataLoader = new DataLoader($oc->academic_year_id, $oc->semester_id);
        $explainer = new ConflictExplainer($dataLoader);

        $reasons = $explainer->explain(
            $data['solution_id'],
            $data['opened_course_id'],
            $data['day'],
            $data['period'],
        );

        return response()->json(['reasons' => $reasons]);
    }

    public function apiFitness($solutionId)
    {
        $solution = TimetableSolution::with('generation')->findOrFail($solutionId);
        $gen = $solution->generation;

        $dataLoader = new DataLoader($gen->academic_year_id, $gen->semester_id);
        $checker = new ConstraintChecker($dataLoader);
        $conflicts = $checker->findAllConflicts($solutionId);

        $hardCount = collect($conflicts)->where('severity', 'hard')->count();
        $softCount = collect($conflicts)->where('severity', 'soft')->count();

        // Update solution
        $solution->update([
            'hard_violations' => $hardCount,
            'soft_violations' => $softCount,
        ]);

        // Refresh conflicts table
        TimetableConflict::where('solution_id', $solutionId)->delete();
        $now = now();
        $records = [];
        foreach ($conflicts as $c) {
            $records[] = [
                'solution_id' => $solutionId,
                'type' => $c['type'],
                'severity' => $c['severity'],
                'day' => $c['day'],
                'period' => $c['period'],
                'details' => json_encode($c['details']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if (!empty($records)) {
            foreach (array_chunk($records, 500) as $chunk) {
                TimetableConflict::insert($chunk);
            }
        }

        return response()->json([
            'hard_violations' => $hardCount,
            'soft_violations' => $softCount,
            'total_conflicts' => count($conflicts),
        ]);
    }

    // ==================== MANUAL SCHEDULING ====================

    public function manualSelect(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        // Get opened classrooms grouped by grade
        $openedCourses = OpenedCourse::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->with('grade.educationLevel', 'classroom', 'course')
            ->get();

        $classroomGroups = $openedCourses
            ->groupBy(fn($oc) => "{$oc->grade_id}_{$oc->classroom_id}")
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'grade_id' => $first->grade_id,
                    'classroom_id' => $first->classroom_id,
                    'grade_name' => $first->grade->name_th ?? '',
                    'classroom_name' => $first->classroom->name ?? '',
                    'education_level' => $first->grade->educationLevel->name_th ?? '',
                    'course_count' => $group->count(),
                    'total_periods' => $group->sum(fn($oc) => $oc->course->periods_per_week ?? 0),
                ];
            })
            ->sortBy('grade_name')
            ->values();

        // Get or create the manual solution
        $solution = $this->getOrCreateManualSolution($yearId, $semesterId);

        // Count entries per classroom
        $entryCounts = TimetableEntry::where('solution_id', $solution->id)
            ->join('opened_courses', 'timetable_entries.opened_course_id', '=', 'opened_courses.id')
            ->selectRaw('opened_courses.grade_id, opened_courses.classroom_id, COUNT(*) as entry_count')
            ->groupBy('opened_courses.grade_id', 'opened_courses.classroom_id')
            ->get()
            ->keyBy(fn($r) => "{$r->grade_id}_{$r->classroom_id}");

        return view('admin.timetable.manual-select', compact('classroomGroups', 'entryCounts', 'solution', 'yearId', 'semesterId'));
    }

    public function manualEditor(Request $request, $gradeId, $classroomId)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $grade = Grade::with('educationLevel')->findOrFail($gradeId);
        $classroom = Classroom::findOrFail($classroomId);

        $solution = $this->getOrCreateManualSolution($yearId, $semesterId);

        // Yearly schedule for this education level
        $schedule = YearlySchedule::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('education_level_id', $grade->education_level_id)
            ->first();

        if (!$schedule) {
            return redirect()->route('admin.timetable.manual.select')
                ->with('error', 'ไม่พบตารางเวลาสำหรับระดับชั้นนี้ กรุณาตั้งค่า Yearly Schedule ก่อน');
        }

        // Opened courses for this classroom
        $openedCourses = OpenedCourse::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('grade_id', $gradeId)
            ->where('classroom_id', $classroomId)
            ->with('course.subjectGroup', 'course.teachers', 'course.rooms.building')
            ->get();

        // Current entries for this classroom
        $entries = TimetableEntry::where('solution_id', $solution->id)
            ->whereHas('openedCourse', fn($q) => $q->where('grade_id', $gradeId)->where('classroom_id', $classroomId))
            ->with('openedCourse.course.subjectGroup', 'teacher', 'room')
            ->get();

        // All teachers schedulable for this term
        $allTeachers = Teacher::where('status', 1)
            ->whereDoesntHave('termStatuses', function ($q) use ($yearId, $semesterId) {
                $q->where('academic_year_id', $yearId)
                  ->where('semester_id', $semesterId)
                  ->where('can_be_scheduled', false);
            })
            ->get();
        $allRooms = Room::where('status', 1)->with('building')->get();

        $allRoomsJson = $allRooms->map(function ($r) {
            return [
                'id' => $r->id,
                'label' => $r->room_number . ($r->building ? ' (' . $r->building->name_th . ')' : ''),
            ];
        })->values();

        // Pre-transform entries for JSON (avoid Blade parsing issues with closures)
        $entriesJson = $entries->map(function ($e) {
            return [
                'id' => $e->id,
                'opened_course_id' => $e->opened_course_id,
                'course_name' => $e->openedCourse->course->name ?? '',
                'subject_group_id' => $e->openedCourse->course->subject_group_id ?? 0,
                'teacher_name' => $e->teacher->name ?? '',
                'teacher_id' => $e->teacher_id,
                'room_number' => $e->room->room_number ?? '',
                'room_id' => $e->room_id,
                'day' => $e->day,
                'period' => $e->period,
                'is_locked' => $e->is_locked,
            ];
        })->values();

        // Schedulable teacher IDs for this term (for JS filtering)
        $schedulableTeacherIds = $allTeachers->pluck('id')->toArray();

        // Build per-course term teachers: teachers assigned via teacher_term_courses for this term
        $termCourseTeachers = TeacherTermCourse::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->whereIn('teacher_id', $schedulableTeacherIds)
            ->with('teacher')
            ->get()
            ->groupBy('course_id');

        return view('admin.timetable.manual-editor', compact(
            'grade', 'classroom', 'solution', 'schedule',
            'openedCourses', 'entries', 'entriesJson', 'allTeachers', 'allRooms', 'allRoomsJson',
            'yearId', 'semesterId', 'schedulableTeacherIds', 'termCourseTeachers'
        ));
    }

    // ==================== HELPERS ====================

    private function getActiveSolution(int $yearId, int $semesterId): ?TimetableSolution
    {
        return TimetableSolution::whereHas('generation', fn($q) => $q->where('academic_year_id', $yearId)->where('semester_id', $semesterId)->whereIn('status', ['completed', 'manual']))
            ->where('is_selected', true)
            ->first();
    }

    private function getOrCreateManualSolution(int $yearId, int $semesterId): TimetableSolution
    {
        // Find existing manual generation
        $generation = TimetableGeneration::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('status', 'manual')
            ->first();

        if (!$generation) {
            $generation = TimetableGeneration::create([
                'academic_year_id' => $yearId,
                'semester_id' => $semesterId,
                'user_id' => auth()->id(),
                'status' => 'manual',
                'population_size' => 0,
                'max_generations' => 0,
                'solutions_requested' => 1,
                'config' => ['type' => 'manual'],
            ]);
        }

        $solution = $generation->solutions()->first();
        if (!$solution) {
            $solution = TimetableSolution::create([
                'generation_id' => $generation->id,
                'rank' => 1,
                'fitness_score' => 0,
                'is_selected' => true,
            ]);
        }

        return $solution;
    }
}
