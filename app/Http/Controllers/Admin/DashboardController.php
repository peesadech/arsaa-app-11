<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CurrentAcademicSetting;
use App\Models\Grade;
use App\Models\OpenedClassroom;
use App\Models\EducationLevel;
use App\Models\GlobalSchedule;
use App\Models\OpenedCourse;
use App\Models\OpenedGrade;
use App\Models\Semester;
use App\Models\YearlySchedule;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $academicYearId = session('current_academic_year_id');
        $semesterId     = session('current_semester_id');

        if (! $academicYearId || ! $semesterId) {
            $global = CurrentAcademicSetting::latest()->first();
            $academicYearId = $global?->academic_year_id;
            $semesterId     = $global?->semester_id;
        }

        $currentYear     = $academicYearId ? AcademicYear::find($academicYearId) : null;
        $currentSemester = $semesterId ? Semester::find($semesterId) : null;

        $openedGrades = ($academicYearId && $semesterId)
            ? OpenedGrade::with('grade')
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->get()
            : collect();

        $openedClassroomCount = ($academicYearId && $semesterId)
            ? OpenedClassroom::where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->count()
            : 0;

        $openedCourseCount = ($academicYearId && $semesterId)
            ? OpenedCourse::where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->select('grade_id', 'course_id')
                ->distinct()
                ->get()
                ->count()
            : 0;

        $openedCourseTotalCount = ($academicYearId && $semesterId)
            ? OpenedCourse::where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->count()
            : 0;

        // Yearly schedule status
        $educationLevels = EducationLevel::where('status', 1)->get();
        $yearlyScheduleMap = ($academicYearId && $semesterId)
            ? YearlySchedule::where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->get()
                ->keyBy('education_level_id')
            : collect();
        $yearlyScheduleTotal = $educationLevels->count();
        $yearlyScheduleConfigured = $yearlyScheduleMap->count();

        return view('admin.dashboard', compact(
            'currentYear', 'currentSemester', 'openedGrades',
            'academicYearId', 'semesterId',
            'openedClassroomCount', 'openedCourseCount', 'openedCourseTotalCount',
            'yearlyScheduleTotal', 'yearlyScheduleConfigured'
        ));
    }

    public function stats()
    {
        $academicYearId = session('current_academic_year_id');
        $semesterId     = session('current_semester_id');

        if (! $academicYearId || ! $semesterId) {
            $global = CurrentAcademicSetting::latest()->first();
            $academicYearId = $global?->academic_year_id;
            $semesterId     = $global?->semester_id;
        }

        $gradeCount = ($academicYearId && $semesterId)
            ? OpenedGrade::where('academic_year_id', $academicYearId)->where('semester_id', $semesterId)->count()
            : 0;

        $classroomCount = ($academicYearId && $semesterId)
            ? OpenedClassroom::where('academic_year_id', $academicYearId)->where('semester_id', $semesterId)->count()
            : 0;

        $courseDistinctCount = ($academicYearId && $semesterId)
            ? OpenedCourse::where('academic_year_id', $academicYearId)->where('semester_id', $semesterId)
                ->select('grade_id', 'course_id')->distinct()->get()->count()
            : 0;

        $courseTotalCount = ($academicYearId && $semesterId)
            ? OpenedCourse::where('academic_year_id', $academicYearId)->where('semester_id', $semesterId)->count()
            : 0;

        return response()->json([
            'grade_count'          => $gradeCount,
            'classroom_count'      => $classroomCount,
            'course_distinct_count' => $courseDistinctCount,
            'course_total_count'   => $courseTotalCount,
        ]);
    }

    public function availableGrades(Request $request)
    {
        $openedMap = OpenedGrade::where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->get(['id', 'grade_id'])
            ->keyBy('grade_id');

        $allClassrooms = Classroom::where('status', 1)->get(['id', 'name']);

        $openedClassroomsByGrade = OpenedClassroom::where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->get(['grade_id', 'classroom_id'])
            ->groupBy('grade_id');

        $grades = Grade::where('status', 1)
            ->get(['id', 'name_th', 'name_en'])
            ->map(fn($g) => [
                'id'         => $g->id,
                'name_th'    => $g->name_th,
                'name_en'    => $g->name_en,
                'is_opened'  => $openedMap->has($g->id),
                'opened_id'  => $openedMap->get($g->id)?->id,
                'classrooms' => $allClassrooms->map(fn($c) => [
                    'id'        => $c->id,
                    'name'      => $c->name,
                    'is_opened' => $openedClassroomsByGrade->get($g->id)
                                    ?->contains('classroom_id', $c->id) ?? false,
                ])->values(),
            ]);

        return response()->json($grades);
    }

    public function coursesByGrade(Request $request)
    {
        $courses = Course::where('grade_id', $request->grade_id)
            ->where('semester_id', $request->semester_id)
            ->where('status', 1)
            ->get(['id', 'name']);

        return response()->json($courses);
    }

    public function openGrade(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id'      => 'required|exists:semesters,id',
            'grade_id'         => 'required|exists:grades,id',
        ]);

        $opened = OpenedGrade::firstOrCreate([
            'academic_year_id' => $request->academic_year_id,
            'semester_id'      => $request->semester_id,
            'grade_id'         => $request->grade_id,
        ]);

        $grade = Grade::find($request->grade_id);

        return response()->json([
            'success' => true,
            'id'      => $opened->id,
            'grade'   => $grade,
        ]);
    }

    public function syncGradeClassrooms(Request $request)
    {
        $request->validate([
            'academic_year_id'         => 'required|exists:academic_years,id',
            'semester_id'              => 'required|exists:semesters,id',
            'grades'                   => 'array',
            'grades.*.grade_id'        => 'required|exists:grades,id',
            'grades.*.classroom_ids'   => 'array',
            'grades.*.classroom_ids.*' => 'exists:classrooms,id',
        ]);

        foreach ($request->grades ?? [] as $gradeData) {
            $gradeId = $gradeData['grade_id'];
            $toOpen  = $gradeData['classroom_ids'] ?? [];

            $isOpened = OpenedGrade::where('academic_year_id', $request->academic_year_id)
                ->where('semester_id', $request->semester_id)
                ->where('grade_id', $gradeId)
                ->exists();

            if (! $isOpened) continue;

            // Sync classrooms
            foreach ($toOpen as $classroomId) {
                OpenedClassroom::firstOrCreate([
                    'academic_year_id' => $request->academic_year_id,
                    'semester_id'      => $request->semester_id,
                    'grade_id'         => $gradeId,
                    'classroom_id'     => $classroomId,
                ]);
            }

            OpenedClassroom::where('academic_year_id', $request->academic_year_id)
                ->where('semester_id', $request->semester_id)
                ->where('grade_id', $gradeId)
                ->whereNotIn('classroom_id', count($toOpen) ? $toOpen : [0])
                ->delete();

            // Sync courses: get all active courses for this grade + semester
            $courseIds = Course::where('grade_id', $gradeId)
                ->where('semester_id', $request->semester_id)
                ->where('status', 1)
                ->pluck('id');

            // Create opened_course for each (classroom × course)
            foreach ($toOpen as $classroomId) {
                foreach ($courseIds as $courseId) {
                    OpenedCourse::firstOrCreate([
                        'academic_year_id' => $request->academic_year_id,
                        'semester_id'      => $request->semester_id,
                        'grade_id'         => $gradeId,
                        'classroom_id'     => $classroomId,
                        'course_id'        => $courseId,
                    ]);
                }
            }

            // Remove opened_courses for classrooms no longer open
            OpenedCourse::where('academic_year_id', $request->academic_year_id)
                ->where('semester_id', $request->semester_id)
                ->where('grade_id', $gradeId)
                ->whereNotIn('classroom_id', count($toOpen) ? $toOpen : [0])
                ->delete();
        }

        $totalOpen = OpenedClassroom::where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->count();

        return response()->json(['success' => true, 'total_open' => $totalOpen]);
    }

    public function closeGrade($id)
    {
        $opened = OpenedGrade::findOrFail($id);

        // remove opened classrooms for this grade/year/semester
        $classroomsRemoved = OpenedClassroom::where('academic_year_id', $opened->academic_year_id)
            ->where('semester_id', $opened->semester_id)
            ->where('grade_id', $opened->grade_id)
            ->count();

        OpenedClassroom::where('academic_year_id', $opened->academic_year_id)
            ->where('semester_id', $opened->semester_id)
            ->where('grade_id', $opened->grade_id)
            ->delete();

        OpenedCourse::where('academic_year_id', $opened->academic_year_id)
            ->where('semester_id', $opened->semester_id)
            ->where('grade_id', $opened->grade_id)
            ->delete();

        $opened->delete();

        return response()->json(['success' => true, 'classrooms_removed' => $classroomsRemoved]);
    }
}
