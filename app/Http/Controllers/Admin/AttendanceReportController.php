<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassSession;
use App\Models\ClassSessionStudent;
use App\Models\Course;
use App\Models\CurrentAcademicSetting;
use App\Models\Grade;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    /** สร้าง query ของ session ตาม filter (ใช้ร่วมกันทั้ง index + drill-down) */
    private function sessionQuery(Request $request, array &$filters)
    {
        $setting = CurrentAcademicSetting::latest()->first();
        $filters = [
            'academic_year_id' => $request->input('academic_year_id', $setting?->academic_year_id),
            'semester_id'      => $request->input('semester_id', $setting?->semester_id),
            'grade_id'         => $request->input('grade_id'),
            'classroom_id'     => $request->input('classroom_id'),
            'course_id'        => $request->input('course_id'),
            'teacher_id'       => $request->input('teacher_id'),
            'date_from'        => $request->input('date_from'),
            'date_to'          => $request->input('date_to'),
        ];

        $q = ClassSession::query()->where('status', '!=', ClassSession::STATUS_CANCELLED);

        foreach (['academic_year_id', 'semester_id', 'grade_id', 'classroom_id', 'course_id', 'teacher_id'] as $key) {
            if (!empty($filters[$key])) {
                $q->where($key, $filters[$key]);
            }
        }
        if (!empty($filters['date_from'])) $q->whereDate('session_date', '>=', $filters['date_from']);
        if (!empty($filters['date_to']))   $q->whereDate('session_date', '<=', $filters['date_to']);

        return $q;
    }

    /** จัดหมวดสถิติจาก record ตาม flag ของ master (ไม่ hardcode ชื่อ) */
    private function tally($records): array
    {
        $t = ['sessions' => $records->count(), 'present' => 0, 'late' => 0, 'leave' => 0, 'absent' => 0, 'activity' => 0];
        foreach ($records as $r) {
            $st = $r->attendanceStatus;
            if (!$st) continue;
            if ($st->is_count_as_present) $t['present']++;
            if ($st->is_late)             $t['late']++;
            if ($st->is_leave)            $t['leave']++;
            if ($st->is_count_as_absent)  $t['absent']++;
            if (in_array($st->status_type, ['ACTIVITY', 'COMPETITION'], true)) $t['activity']++;
        }
        $t['percent'] = $t['sessions'] > 0 ? round($t['present'] / $t['sessions'] * 100, 1) : 0.0;
        return $t;
    }

    public function index(Request $request)
    {
        $filters = [];
        $sessionQuery = $this->sessionQuery($request, $filters);
        $sessionIds = $sessionQuery->pluck('id');

        $rows = collect();
        $summary = ['sessions' => $sessionIds->count(), 'students' => 0, 'present' => 0, 'late' => 0, 'leave' => 0, 'absent' => 0, 'activity' => 0, 'percent' => 0.0];

        if ($sessionIds->isNotEmpty()) {
            $records = ClassSessionStudent::whereIn('class_session_id', $sessionIds)
                ->with('attendanceStatus')
                ->get()
                ->groupBy('student_id');

            $students = Student::whereIn('id', $records->keys())->get()->keyBy('id');

            $rows = $records->map(function ($recs, $studentId) use ($students) {
                $t = $this->tally($recs);
                $t['student'] = $students->get($studentId);
                $t['student_id'] = $studentId;
                return $t;
            })->filter(fn ($r) => $r['student'])->sortBy(fn ($r) => $r['student']->name_th)->values();

            $summary['students'] = $rows->count();
            foreach (['present', 'late', 'leave', 'absent', 'activity'] as $k) {
                $summary[$k] = $rows->sum($k);
            }
            $totalMarked = $rows->sum('sessions');
            $summary['percent'] = $totalMarked > 0 ? round($summary['present'] / $totalMarked * 100, 1) : 0.0;
        }

        $threshold = (float) config('attendance.min_percent', 80);
        $summary['below'] = $rows->filter(fn ($r) => $r['sessions'] > 0 && $r['percent'] < $threshold)->count();

        return view('admin.attendance-reports.index', array_merge(
            compact('rows', 'summary', 'filters', 'threshold'),
            $this->filterOptions()
        ));
    }

    /** Drill-down: รายละเอียดการเข้าเรียนของนักเรียนคนเดียวตาม filter เดิม */
    public function student(Request $request, $studentId)
    {
        $filters = [];
        $sessionQuery = $this->sessionQuery($request, $filters);
        $student = Student::findOrFail($studentId);

        $sessionIds = $sessionQuery->pluck('id');
        $records = ClassSessionStudent::whereIn('class_session_id', $sessionIds)
            ->where('student_id', $studentId)
            ->with(['attendanceStatus', 'classSession.course', 'classSession.classroom', 'classSession.grade'])
            ->get()
            ->sortByDesc(fn ($r) => optional($r->classSession)->session_date)
            ->values();

        $tally = $this->tally($records);

        return view('admin.attendance-reports.student', compact('student', 'records', 'tally', 'filters'));
    }

    private function filterOptions(): array
    {
        return [
            'academicYears' => AcademicYear::orderByDesc('year')->get(),
            'semesters'     => Semester::orderBy('semester_number')->get(),
            'grades'        => Grade::orderBy('name_th')->get(),
            'classrooms'    => Classroom::orderBy('name')->get(),
            'courses'       => Course::orderBy('name')->get(),
            'teachers'      => Teacher::where('status', 1)->orderBy('name')->get(),
        ];
    }
}
