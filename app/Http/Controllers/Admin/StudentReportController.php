<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\CurrentAcademicSetting;
use App\Models\MasterOption;
use App\Models\OpenedCourse;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentScore;
use Illuminate\Http\Request;

class StudentReportController extends Controller
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

        $academicYears = AcademicYear::orderByDesc('year')->get();
        $semesters = Semester::where('status', 1)->get();
        $classrooms = Classroom::where('status', 1)->get();

        return view('admin.student-reports.index', compact('academicYears', 'semesters', 'classrooms', 'yearId', 'semesterId'));
    }

    /**
     * Export รายชื่อนักเรียนเป็น CSV (เปิดด้วย Excel ได้)
     */
    public function studentsCsv(Request $request)
    {
        $students = Student::with('nationality', 'religion', 'bloodType')
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when(
                $request->filled('academic_year_id') || $request->filled('semester_id') || $request->filled('classroom_id'),
                function ($q) use ($request) {
                    $q->whereHas('enrollments', function ($qq) use ($request) {
                        $qq->where('status', 'enrolled');
                        if ($request->filled('academic_year_id')) $qq->where('academic_year_id', $request->academic_year_id);
                        if ($request->filled('semester_id')) $qq->where('semester_id', $request->semester_id);
                        if ($request->filled('classroom_id')) $qq->where('classroom_id', $request->classroom_id);
                    });
                }
            )
            ->orderBy('student_code')
            ->get();

        $rows = [];
        $rows[] = [__('Student Code'), __('Name (TH)'), __('Chinese Name'), __('Citizen ID'), __('Birth Date'), __('Age'), __('Nationality'), __('Religion'), __('Blood Type'), __('Mobile'), __('Status')];

        foreach ($students as $s) {
            $rows[] = [
                $s->student_code, $s->name_th, $s->name_cn, $s->citizen_id,
                $s->birth_date?->format('d/m/Y'), $s->age,
                $s->nationality?->name_th, $s->religion?->name_th, $s->bloodType?->name_th,
                $s->mobile ?: $s->phone, __(ucfirst($s->status)),
            ];
        }

        $csv = "\xEF\xBB\xBF"; // BOM ให้ Excel อ่านภาษาไทยถูก
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v ?? '') . '"', $row)) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="students.csv"');
    }

    /**
     * ใบประวัตินักเรียนรายบุคคล (หน้า print)
     */
    public function profile($id)
    {
        $student = Student::with([
            'race', 'nationality', 'religion', 'bloodType',
            'addresses.province', 'guardians.guardianType', 'guardians.nationality',
            'educationHistories', 'documents.documentType',
            'enrollments.academicYear', 'enrollments.semester', 'enrollments.grade', 'enrollments.classroom',
        ])->findOrFail($id);

        $documentTypes = MasterOption::options(MasterOption::TYPE_DOCUMENT_TYPE);

        return view('admin.student-reports.profile', compact('student', 'documentTypes'));
    }

    /**
     * Transcript / ใบสรุปผลการเรียนรายนักเรียน (หน้า print)
     */
    public function transcript($id)
    {
        $student = Student::findOrFail($id);

        $scores = StudentScore::where('student_id', $id)
            ->with('openedCourse.course.subjectGroup', 'openedCourse.academicYear', 'openedCourse.semester', 'openedCourse.grade', 'openedCourse.classroom', 'teacher')
            ->get()
            ->sortBy([
                fn($a, $b) => ($a->openedCourse->academicYear->year ?? 0) <=> ($b->openedCourse->academicYear->year ?? 0),
                fn($a, $b) => ($a->openedCourse->semester->semester_number ?? 0) <=> ($b->openedCourse->semester->semester_number ?? 0),
            ])
            ->groupBy(fn($s) => ($s->openedCourse->academicYear->year ?? '?') . ' / ' . ($s->openedCourse->semester->semester_number ?? '?'));

        return view('admin.student-reports.transcript', compact('student', 'scores'));
    }

    /**
     * รายงานผลการเรียนรายห้อง (นักเรียน × วิชา)
     */
    public function classScores(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'grade_id' => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $openedCourses = OpenedCourse::where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->where('grade_id', $request->grade_id)
            ->where('classroom_id', $request->classroom_id)
            ->with('course', 'academicYear', 'semester', 'grade', 'classroom')
            ->get();

        $enrollments = StudentEnrollment::where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->where('grade_id', $request->grade_id)
            ->where('classroom_id', $request->classroom_id)
            ->where('status', StudentEnrollment::STATUS_ENROLLED)
            ->with('student')
            ->orderBy(Student::select('name_th')->whereColumn('students.id', 'student_enrollments.student_id'))
            ->get();

        $scores = StudentScore::whereIn('opened_course_id', $openedCourses->pluck('id'))
            ->get()
            ->keyBy(fn($s) => $s->student_id . '-' . $s->opened_course_id);

        return view('admin.student-reports.class-scores', compact('openedCourses', 'enrollments', 'scores'));
    }

    /**
     * รายงานนักเรียนที่เอกสารสมัครยังไม่ครบ
     */
    public function incompleteDocuments()
    {
        $documentTypes = MasterOption::options(MasterOption::TYPE_DOCUMENT_TYPE);
        $totalTypes = $documentTypes->count();

        $students = Student::where('status', Student::STATUS_STUDYING)
            ->with('documents.documentType')
            ->orderBy('student_code')
            ->get()
            ->map(function ($s) use ($totalTypes) {
                $received = $s->documents->where('is_received', true)->count();
                $s->received_count = $received;
                $s->missing_count = max(0, $totalTypes - $received);
                return $s;
            })
            ->filter(fn($s) => $s->missing_count > 0)
            ->values();

        return view('admin.student-reports.incomplete-documents', compact('students', 'documentTypes', 'totalTypes'));
    }
}
