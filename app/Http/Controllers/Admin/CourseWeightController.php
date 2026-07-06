<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseWeight;
use App\Models\CurrentAcademicSetting;
use App\Models\Grade;
use App\Models\OpenedGrade;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseWeightController extends Controller
{
    private function currentYearSemester(): array
    {
        $yearId = session('current_academic_year_id');
        $semId  = session('current_semester_id');

        if (! $yearId || ! $semId) {
            $global = CurrentAcademicSetting::latest()->first();
            $yearId = $global?->academic_year_id;
            $semId  = $global?->semester_id;
        }

        return [$yearId, $semId];
    }

    /**
     * เลือกระดับชั้น → กำหนดสัดส่วน/น้ำหนักรายวิชา (รวมควร = 100) ตามปี+เทอมปัจจุบัน
     */
    public function index(Request $request)
    {
        [$curYearId, $curSemId] = $this->currentYearSemester();

        // เลือกปี+เทอมจาก query ได้ (default = เทอมปัจจุบัน) — แยกกำหนดน้ำหนักได้รายเทอม
        $yearId     = (int) ($request->query('academic_year_id') ?: $curYearId);
        $semesterId = (int) ($request->query('semester_id') ?: $curSemId);

        $academicYears = AcademicYear::orderByDesc('year')->get();
        $semesters     = Semester::orderBy('semester_number')->get();

        $academicYear = $yearId ? AcademicYear::find($yearId) : null;
        $semester     = $semesterId ? Semester::find($semesterId) : null;

        // ระดับชั้นที่เปิดในเทอมนี้ (fallback = ระดับชั้น active ทั้งหมด)
        $grades = OpenedGrade::with('grade')
            ->where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->get()
            ->map(fn($og) => $og->grade)
            ->filter()
            ->unique('id')
            ->values();

        if ($grades->isEmpty()) {
            $grades = Grade::where('status', 1)->get();
        }

        $selectedGradeId = (int) $request->query('grade_id');

        $courses = collect();
        $weights = collect();
        if ($selectedGradeId && $yearId && $semesterId) {
            $courses = Course::where('grade_id', $selectedGradeId)
                ->where('semester_id', $semesterId)
                ->where('status', 1)
                ->orderBy('name')
                ->get();

            $weights = CourseWeight::where('academic_year_id', $yearId)
                ->where('semester_id', $semesterId)
                ->where('grade_id', $selectedGradeId)
                ->get()
                ->keyBy('course_id');
        }

        // ปี/เทอมอื่นที่เคยบันทึกน้ำหนักของระดับชั้นนี้ไว้ — ใช้เป็นตัวเลือกคัดลอกค่า
        $existingSources = collect();
        if ($selectedGradeId) {
            $yearNames = $academicYears->pluck('year', 'id');
            $semNames  = $semesters->pluck('semester_number', 'id');

            // คัดลอกได้เฉพาะ "ปีอื่น เทอมเดียวกัน" เพราะรายวิชาแยกตามเทอม (course_id ต่างกัน)
            // ข้ามเทอมชุดวิชาไม่ตรงกัน จับคู่ไม่ได้
            $existingSources = CourseWeight::where('grade_id', $selectedGradeId)
                ->where('semester_id', $semesterId)
                ->where('academic_year_id', '!=', $yearId)
                ->select('academic_year_id', 'semester_id')
                ->distinct()
                ->get()
                ->map(function ($r) use ($yearNames, $semNames, $selectedGradeId, $courses) {
                    // preview: น้ำหนักของแต่ละวิชาในปี/เทอมนั้น (เรียงตามวิชาปัจจุบัน — เทอมเดียวกัน id ตรงกัน)
                    $srcWeights = CourseWeight::where('academic_year_id', $r->academic_year_id)
                        ->where('semester_id', $r->semester_id)
                        ->where('grade_id', $selectedGradeId)
                        ->get()
                        ->keyBy('course_id');

                    $details = $courses->map(fn($c) => [
                        'name'   => $c->name,
                        'weight' => isset($srcWeights[$c->id]) ? (float) $srcWeights[$c->id]->weight : null,
                    ])->values();

                    return [
                        'academic_year_id' => $r->academic_year_id,
                        'semester_id'      => $r->semester_id,
                        'year'             => $yearNames[$r->academic_year_id] ?? $r->academic_year_id,
                        'semester_number'  => $semNames[$r->semester_id] ?? $r->semester_id,
                        'details'          => $details,
                        'total'            => round($details->sum(fn($d) => $d['weight'] ?? 0), 2),
                    ];
                })
                ->values();
        }

        return view('admin.course-weights.index', compact(
            'academicYear', 'semester', 'yearId', 'semesterId',
            'academicYears', 'semesters',
            'grades', 'selectedGradeId', 'courses', 'weights', 'existingSources'
        ));
    }

    /**
     * คืนค่าน้ำหนักของปี/เทอม/ระดับชั้นที่ระบุ (JSON) — ใช้คัดลอกมาเติมในฟอร์ม
     */
    public function copySource(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => 'required|integer',
            'semester_id'      => 'required|integer',
            'grade_id'         => 'required|integer',
        ]);

        $weights = CourseWeight::where('academic_year_id', $data['academic_year_id'])
            ->where('semester_id', $data['semester_id'])
            ->where('grade_id', $data['grade_id'])
            ->get(['course_id', 'weight'])
            ->mapWithKeys(fn($r) => [$r->course_id => (float) $r->weight]);

        return response()->json(['weights' => $weights]);
    }

    /**
     * บันทึกสัดส่วนรายวิชาทั้งระดับชั้น
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id'      => 'required|exists:semesters,id',
            'grade_id'         => 'required|exists:grades,id',
            'weights'          => 'required|array',
            'weights.*'        => 'nullable|numeric|min:0|max:100',
        ]);

        $yearId     = (int) $data['academic_year_id'];
        $semesterId = (int) $data['semester_id'];
        $gradeId    = (int) $data['grade_id'];

        // รวมน้ำหนักต้องเท่ากับ 100 เท่านั้นจึงจะบันทึกได้
        $sum = collect($data['weights'])->reduce(
            fn($carry, $w) => $carry + (($w !== null && $w !== '') ? (float) $w : 0),
            0.0
        );
        if (abs(round($sum, 2) - 100) >= 0.01) {
            return back()->withInput()->withErrors([
                'weights' => __('Total weight must equal 100 before saving. (current: :sum)', ['sum' => round($sum, 2)]),
            ]);
        }

        DB::transaction(function () use ($data, $yearId, $semesterId, $gradeId) {
            foreach ($data['weights'] as $courseId => $weight) {
                // เฉพาะวิชาที่อยู่ในระดับชั้น+เทอมนี้จริง
                $valid = Course::where('id', $courseId)
                    ->where('grade_id', $gradeId)
                    ->where('semester_id', $semesterId)
                    ->exists();
                if (! $valid) {
                    continue;
                }

                if ($weight === null || $weight === '') {
                    CourseWeight::where('academic_year_id', $yearId)
                        ->where('semester_id', $semesterId)
                        ->where('grade_id', $gradeId)
                        ->where('course_id', $courseId)
                        ->delete();
                    continue;
                }

                CourseWeight::updateOrCreate(
                    [
                        'academic_year_id' => $yearId,
                        'semester_id'      => $semesterId,
                        'grade_id'         => $gradeId,
                        'course_id'        => $courseId,
                    ],
                    ['weight' => $weight, 'updated_by' => Auth::id()]
                );
            }
        });

        return redirect()->route('admin.course-weights.index', [
            'academic_year_id' => $yearId,
            'semester_id'      => $semesterId,
            'grade_id'         => $gradeId,
        ])->with('status', __('Subject weights saved'));
    }
}
