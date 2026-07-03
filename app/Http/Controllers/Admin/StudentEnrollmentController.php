<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CurrentAcademicSetting;
use App\Models\OpenedClassroom;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentController extends Controller
{
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

    public function index(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $academicYear = AcademicYear::find($yearId);
        $semester = Semester::find($semesterId);

        // ห้องที่เปิดในเทอมนี้ (ของระบบเดิม)
        $openedClassrooms = OpenedClassroom::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->with('grade', 'classroom')
            ->get();

        // จำนวนนักเรียนต่อห้อง
        $counts = StudentEnrollment::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('status', StudentEnrollment::STATUS_ENROLLED)
            ->selectRaw('grade_id, classroom_id, count(*) as total')
            ->groupBy('grade_id', 'classroom_id')
            ->get()
            ->keyBy(fn($r) => $r->grade_id . '-' . $r->classroom_id);

        $selectedGradeId = (int) $request->query('grade_id');
        $selectedClassroomId = (int) $request->query('classroom_id');

        $enrollments = collect();
        if ($selectedGradeId && $selectedClassroomId) {
            $enrollments = StudentEnrollment::where('academic_year_id', $yearId)
                ->where('semester_id', $semesterId)
                ->where('grade_id', $selectedGradeId)
                ->where('classroom_id', $selectedClassroomId)
                ->where('status', StudentEnrollment::STATUS_ENROLLED)
                ->with('student')
                ->orderBy(Student::select('name_th')->whereColumn('students.id', 'student_enrollments.student_id'))
                ->get();
        }

        return view('admin.student-enrollments.index', compact(
            'academicYear', 'semester', 'yearId', 'semesterId',
            'openedClassrooms', 'counts', 'selectedGradeId', 'selectedClassroomId', 'enrollments'
        ));
    }

    /**
     * ค้นหานักเรียนที่ยังไม่ถูกจัดเข้าห้องในเทอมนี้ (สำหรับ modal เพิ่มนักเรียน)
     */
    public function searchStudents(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $enrolledIds = StudentEnrollment::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('status', StudentEnrollment::STATUS_ENROLLED)
            ->pluck('student_id');

        $students = Student::where('status', Student::STATUS_STUDYING)
            ->whereNotIn('id', $enrolledIds)
            ->when($request->filled('keyword'), function ($q) use ($request) {
                $kw = $request->keyword;
                $q->where(fn($qq) => $qq->where('student_code', 'like', "%{$kw}%")
                    ->orWhere('name_th', 'like', "%{$kw}%")
                    ->orWhere('name_cn', 'like', "%{$kw}%"));
            })
            ->orderBy('name_th')
            ->paginate(10, ['id', 'student_code', 'name_th', 'name_cn', 'image_path']);

        return response()->json($students);
    }

    public function store(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $data = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $added = 0;
        foreach ($data['student_ids'] as $studentId) {
            // กันซ้ำ: นักเรียนต้องยังไม่มีห้อง active ในเทอมนี้
            $exists = StudentEnrollment::where('student_id', $studentId)
                ->where('academic_year_id', $yearId)
                ->where('semester_id', $semesterId)
                ->where('status', StudentEnrollment::STATUS_ENROLLED)
                ->exists();
            if ($exists) continue;

            StudentEnrollment::create([
                'student_id' => $studentId,
                'academic_year_id' => $yearId,
                'semester_id' => $semesterId,
                'grade_id' => $data['grade_id'],
                'classroom_id' => $data['classroom_id'],
                'status' => StudentEnrollment::STATUS_ENROLLED,
                'enrolled_at' => now(),
                'created_by' => Auth::id(),
            ]);
            $added++;
        }

        return back()->with('status', __(':count students enrolled', ['count' => $added]));
    }

    /**
     * ย้ายห้อง: ปิด enrollment เดิม (moved) แล้วสร้างรายการใหม่
     */
    public function move(Request $request, $enrollmentId)
    {
        $enrollment = StudentEnrollment::findOrFail($enrollmentId);

        $data = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($enrollment, $data) {
            $enrollment->update(['status' => StudentEnrollment::STATUS_MOVED]);

            StudentEnrollment::create([
                'student_id' => $enrollment->student_id,
                'academic_year_id' => $enrollment->academic_year_id,
                'semester_id' => $enrollment->semester_id,
                'grade_id' => $data['grade_id'],
                'classroom_id' => $data['classroom_id'],
                'status' => StudentEnrollment::STATUS_ENROLLED,
                'enrolled_at' => now(),
                'note' => $data['note'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });

        return back()->with('status', __('Student moved successfully'));
    }

    public function remove($enrollmentId)
    {
        $enrollment = StudentEnrollment::findOrFail($enrollmentId);
        $enrollment->update(['status' => StudentEnrollment::STATUS_LEFT]);

        return back()->with('status', __('Student removed from classroom'));
    }
}
