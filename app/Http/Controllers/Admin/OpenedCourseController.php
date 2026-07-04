<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CurrentAcademicSetting;
use App\Models\Grade;
use App\Models\OpenedClassroom;
use App\Models\OpenedCourse;
use App\Models\OpenedGrade;
use App\Models\Semester;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OpenedCourseController extends Controller
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

    public function index(Request $request)
    {
        [$academicYearId, $semesterId] = $this->currentYearSemester();

        $currentYear     = $academicYearId ? AcademicYear::find($academicYearId) : null;
        $currentSemester = $semesterId ? Semester::find($semesterId) : null;

        $query = OpenedCourse::with(['grade', 'classroom', 'course'])
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId);

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereHas('course', fn ($c) => $c->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('grade', fn ($c) => $c->where('name_th', 'like', "%{$s}%"))
                  ->orWhereHas('classroom', fn ($c) => $c->where('name', 'like', "%{$s}%"));
            });
        }

        $sortBy = in_array($request->get('sort_by'), ['id', 'grade_id', 'classroom_id', 'course_id'])
            ? $request->get('sort_by') : 'id';
        $sortOrder = $request->get('sort_order') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) $request->get('per_page', 10);
        $openedCourses = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.opened-courses._rows', compact('openedCourses'))->render(),
                'meta' => [
                    'total'        => $openedCourses->total(),
                    'per_page'     => $openedCourses->perPage(),
                    'current_page' => $openedCourses->currentPage(),
                    'last_page'    => $openedCourses->lastPage(),
                    'from'         => $openedCourses->firstItem() ?? 0,
                    'to'           => $openedCourses->lastItem() ?? 0,
                ],
            ]);
        }

        $grades = Grade::where('status', 1)->get();
        $courses = $semesterId
            ? Course::where('status', 1)->where('semester_id', $semesterId)->orderBy('name')->get()
            : collect();

        return view('admin.opened-courses.index', compact(
            'openedCourses', 'currentYear', 'currentSemester',
            'academicYearId', 'semesterId', 'grades', 'courses'
        ));
    }

    public function data(Request $request)
    {
        [$academicYearId, $semesterId] = $this->currentYearSemester();

        $query = OpenedCourse::with(['grade', 'classroom', 'course'])
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->select('opened_courses.*');

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        return DataTables::of($query)
            ->addColumn('grade_name',     fn($row) => $row->grade?->name_th ?? '-')
            ->addColumn('classroom_name', fn($row) => $row->classroom?->name ?? '-')
            ->addColumn('course_name',    fn($row) => $row->course?->name ?? '-')
            ->addColumn('actions',        fn($row) => $row->id)
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create()
    {
        [$academicYearId, $semesterId] = $this->currentYearSemester();

        $currentYear     = $academicYearId ? AcademicYear::find($academicYearId) : null;
        $currentSemester = $semesterId ? Semester::find($semesterId) : null;

        if (! $currentYear || ! $currentSemester) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'กรุณาเลือกปีการศึกษาและภาคเรียนก่อนดำเนินการ');
        }

        $openedGrades = ($academicYearId && $semesterId)
            ? OpenedGrade::with('grade')
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->get()
            : collect();

        $classrooms = ($academicYearId && $semesterId)
            ? OpenedClassroom::with('classroom')
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->get()
            : collect();

        $courses = $semesterId
            ? Course::where('semester_id', $semesterId)->where('status', 1)->orderBy('name')->get()
            : collect();

        return view('admin.opened-courses.create', compact(
            'currentYear', 'currentSemester', 'academicYearId', 'semesterId',
            'openedGrades', 'classrooms', 'courses'
        ));
    }

    public function store(Request $request)
    {
        [$academicYearId, $semesterId] = $this->currentYearSemester();

        $request->validate([
            'grade_id'     => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'course_id'    => 'required|exists:courses,id',
        ]);

        $exists = OpenedCourse::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('grade_id', $request->grade_id)
            ->where('classroom_id', $request->classroom_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['course_id' => 'รายวิชานี้เปิดสอนในห้องนี้แล้ว'])->withInput();
        }

        OpenedCourse::create([
            'academic_year_id' => $academicYearId,
            'semester_id'      => $semesterId,
            'grade_id'         => $request->grade_id,
            'classroom_id'     => $request->classroom_id,
            'course_id'        => $request->course_id,
        ]);

        return redirect()->route('admin.opened-courses.index')
            ->with('success', 'เพิ่มรายวิชาที่เปิดสอนเรียบร้อยแล้ว');
    }

    public function edit($id)
    {
        [$academicYearId, $semesterId] = $this->currentYearSemester();

        $openedCourse    = OpenedCourse::with(['grade', 'classroom', 'course'])->findOrFail($id);
        $currentYear     = $academicYearId ? AcademicYear::find($academicYearId) : null;
        $currentSemester = $semesterId ? Semester::find($semesterId) : null;

        if (! $currentYear || ! $currentSemester) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'กรุณาเลือกปีการศึกษาและภาคเรียนก่อนดำเนินการ');
        }

        $openedGrades = ($academicYearId && $semesterId)
            ? OpenedGrade::with('grade')
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->get()
            : collect();

        $classrooms = ($academicYearId && $semesterId)
            ? OpenedClassroom::with('classroom')
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->get()
            : collect();

        $courses = $semesterId
            ? Course::where('semester_id', $semesterId)->where('status', 1)->orderBy('name')->get()
            : collect();

        return view('admin.opened-courses.edit', compact(
            'openedCourse', 'currentYear', 'currentSemester',
            'academicYearId', 'semesterId', 'openedGrades', 'classrooms', 'courses'
        ));
    }

    public function update(Request $request, $id)
    {
        [$academicYearId, $semesterId] = $this->currentYearSemester();

        $openedCourse = OpenedCourse::findOrFail($id);

        $request->validate([
            'grade_id'     => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'course_id'    => 'required|exists:courses,id',
        ]);

        $exists = OpenedCourse::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('grade_id', $request->grade_id)
            ->where('classroom_id', $request->classroom_id)
            ->where('course_id', $request->course_id)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['course_id' => 'รายวิชานี้เปิดสอนในห้องนี้แล้ว'])->withInput();
        }

        $openedCourse->update([
            'grade_id'     => $request->grade_id,
            'classroom_id' => $request->classroom_id,
            'course_id'    => $request->course_id,
        ]);

        return redirect()->route('admin.opened-courses.index')
            ->with('success', 'แก้ไขรายวิชาที่เปิดสอนเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        OpenedCourse::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    public function classroomsByGrade(Request $request)
    {
        [$academicYearId, $semesterId] = $this->currentYearSemester();

        $classrooms = OpenedClassroom::with('classroom')
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('grade_id', $request->grade_id)
            ->get()
            ->map(fn($oc) => [
                'id'   => $oc->classroom_id,
                'name' => $oc->classroom?->name,
            ]);

        return response()->json($classrooms);
    }

    public function coursesByGrade(Request $request)
    {
        [$academicYearId, $semesterId] = $this->currentYearSemester();

        $courses = Course::where('grade_id', $request->grade_id)
            ->where('semester_id', $semesterId)
            ->where('status', 1)
            ->get(['id', 'name']);

        return response()->json($courses);
    }
}
