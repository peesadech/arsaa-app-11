<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\BehaviorScore;
use App\Models\CurrentAcademicSetting;
use App\Models\OpenedClassroom;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentBehaviorRecord;
use App\Models\StudentEnrollment;
use App\Services\TeacherAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BehaviorRecordController extends Controller
{
    public function __construct(private TeacherAccountService $accountService)
    {
    }

    private function resolveYearSemester(Request $request): array
    {
        $yearId = $request->session()->get('current_academic_year_id');
        $semesterId = $request->session()->get('current_semester_id');

        if (!$yearId || !$semesterId) {
            $setting = CurrentAcademicSetting::latest()->first();
            $yearId = $setting?->academic_year_id;
            $semesterId = $setting?->semester_id;
        }

        return [$yearId, $semesterId];
    }

    private function isAdmin(): bool
    {
        return Auth::user()->getRoleNames()
            ->map(fn($r) => strtoupper($r))
            ->intersect(['ADMIN', 'SUPERADMIN'])
            ->isNotEmpty();
    }

    /** ห้องที่ผู้ใช้เข้าถึงได้: admin = ทุกห้อง, ครู = เฉพาะห้องที่เป็นครูประจำชั้น */
    private function accessibleClassrooms(int $yearId, int $semesterId)
    {
        $query = OpenedClassroom::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->with('grade', 'classroom');

        if (!$this->isAdmin()) {
            $teacher = $this->accountService->teacherForUser(Auth::user());
            abort_unless($teacher, 403, __('No teacher record linked to this account'));
            $query->whereHas('homeroomTeachers', fn($q) => $q->where('teachers.id', $teacher->id));
        }

        return $query->get();
    }

    private function canAccess(int $gradeId, int $classroomId, int $yearId, int $semesterId): bool
    {
        return $this->accessibleClassrooms($yearId, $semesterId)
            ->contains(fn($oc) => $oc->grade_id == $gradeId && $oc->classroom_id == $classroomId);
    }

    public function index(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $academicYear = AcademicYear::find($yearId);
        $semester = Semester::find($semesterId);

        $openedClassrooms = ($yearId && $semesterId)
            ? $this->accessibleClassrooms($yearId, $semesterId)
            : collect();

        $selectedGradeId = (int) $request->query('grade_id');
        $selectedClassroomId = (int) $request->query('classroom_id');

        $enrollments = collect();
        $records = collect();
        $meritItems = collect();
        $demeritItems = collect();

        if ($selectedGradeId && $selectedClassroomId
            && $this->canAccess($selectedGradeId, $selectedClassroomId, $yearId, $semesterId)) {

            $enrollments = StudentEnrollment::where('academic_year_id', $yearId)
                ->where('semester_id', $semesterId)
                ->where('grade_id', $selectedGradeId)
                ->where('classroom_id', $selectedClassroomId)
                ->where('status', StudentEnrollment::STATUS_ENROLLED)
                ->with('student')
                ->orderBy(Student::select('name_th')->whereColumn('students.id', 'student_enrollments.student_id'))
                ->get();

            $records = StudentBehaviorRecord::where('academic_year_id', $yearId)
                ->where('semester_id', $semesterId)
                ->where('grade_id', $selectedGradeId)
                ->where('classroom_id', $selectedClassroomId)
                ->orderByDesc('recorded_at')
                ->orderByDesc('id')
                ->get()
                ->groupBy('student_id');

            $meritItems = BehaviorScore::type(BehaviorScore::TYPE_MERIT)->active()->orderBy('sort_order')->orderBy('id')->get();
            $demeritItems = BehaviorScore::type(BehaviorScore::TYPE_DEMERIT)->active()->orderBy('sort_order')->orderBy('id')->get();
        }

        return view('behavior-records.index', compact(
            'academicYear', 'semester', 'yearId', 'semesterId', 'openedClassrooms',
            'selectedGradeId', 'selectedClassroomId', 'enrollments', 'records',
            'meritItems', 'demeritItems'
        ));
    }

    public function store(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $data = $request->validate([
            'grade_id'          => 'required|integer',
            'classroom_id'      => 'required|integer',
            'student_id'        => 'required|exists:students,id',
            'behavior_score_id' => 'required|exists:behavior_scores,id',
            'note'              => 'nullable|string|max:255',
        ]);

        abort_unless(
            $this->canAccess((int) $data['grade_id'], (int) $data['classroom_id'], $yearId, $semesterId),
            403,
            __('You can only record for your own classrooms')
        );

        // นักเรียนต้องอยู่ในห้องนี้จริง
        $inRoom = StudentEnrollment::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('grade_id', $data['grade_id'])
            ->where('classroom_id', $data['classroom_id'])
            ->where('student_id', $data['student_id'])
            ->where('status', StudentEnrollment::STATUS_ENROLLED)
            ->exists();
        abort_unless($inRoom, 422, __('Student is not in this classroom'));

        $item = BehaviorScore::findOrFail($data['behavior_score_id']);

        StudentBehaviorRecord::create([
            'student_id'        => $data['student_id'],
            'academic_year_id'  => $yearId,
            'semester_id'       => $semesterId,
            'grade_id'          => $data['grade_id'],
            'classroom_id'      => $data['classroom_id'],
            'behavior_score_id' => $item->id,
            'type'              => $item->type,
            'name'              => $item->name,
            'score'             => $item->score,
            'note'              => $data['note'] ?? null,
            'recorded_by'       => Auth::id(),
            'recorded_at'       => now()->toDateString(),
        ]);

        return redirect()->route('behavior-records.index', [
            'grade_id' => $data['grade_id'],
            'classroom_id' => $data['classroom_id'],
        ])->with('status', __('Saved successfully'));
    }

    public function destroy(Request $request, $id)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $record = StudentBehaviorRecord::findOrFail($id);

        abort_unless(
            $this->canAccess($record->grade_id, $record->classroom_id, $record->academic_year_id, $record->semester_id),
            403
        );

        $record->delete();

        return redirect()->route('behavior-records.index', [
            'grade_id' => $record->grade_id,
            'classroom_id' => $record->classroom_id,
        ])->with('status', __('Deleted successfully'));
    }
}
