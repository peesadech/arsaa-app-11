<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Room;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\TimetableSolution;
use App\Models\YearlySchedule;
use Illuminate\Http\Request;

class TimetableExportController extends Controller
{
    private const DAY_NAMES = [1=>'จันทร์', 2=>'อังคาร', 3=>'พุธ', 4=>'พฤหัสบดี', 5=>'ศุกร์', 6=>'เสาร์', 7=>'อาทิตย์'];

    public function exportClassroomPdf(Request $request)
    {
        $data = $request->validate([
            'solution_id' => 'required|integer',
            'classroom_id' => 'required|integer',
            'grade_id' => 'required|integer',
        ]);

        $solution = TimetableSolution::with('generation')->findOrFail($data['solution_id']);
        $classroom = Classroom::findOrFail($data['classroom_id']);
        $grade = Grade::with('educationLevel')->findOrFail($data['grade_id']);

        $schedule = YearlySchedule::where('academic_year_id', $solution->generation->academic_year_id)
            ->where('semester_id', $solution->generation->semester_id)
            ->where('education_level_id', $grade->education_level_id)
            ->first();

        $entries = TimetableEntry::where('solution_id', $data['solution_id'])
            ->whereHas('openedCourse', fn($q) => $q->where('classroom_id', $data['classroom_id'])->where('grade_id', $data['grade_id']))
            ->with('openedCourse.course', 'teacher', 'room')
            ->get();

        $html = $this->buildGridHtml(
            "ตารางเรียน {$grade->name_th} / {$classroom->name}",
            $schedule, $entries, 'classroom'
        );

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', "inline; filename=\"timetable_{$grade->name_th}_{$classroom->name}.html\"");
    }

    public function exportTeacherPdf(Request $request)
    {
        $data = $request->validate([
            'solution_id' => 'required|integer',
            'teacher_id' => 'required|integer',
        ]);

        $solution = TimetableSolution::with('generation')->findOrFail($data['solution_id']);
        $teacher = Teacher::findOrFail($data['teacher_id']);

        $schedule = YearlySchedule::where('academic_year_id', $solution->generation->academic_year_id)
            ->where('semester_id', $solution->generation->semester_id)
            ->first();

        $entries = TimetableEntry::where('solution_id', $data['solution_id'])
            ->where('teacher_id', $data['teacher_id'])
            ->with('openedCourse.course', 'openedCourse.classroom', 'openedCourse.grade', 'room')
            ->get();

        $html = $this->buildGridHtml(
            "ตารางสอน — ครู{$teacher->name}",
            $schedule, $entries, 'teacher'
        );

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', "inline; filename=\"timetable_teacher_{$teacher->name}.html\"");
    }

    public function exportExcel(Request $request)
    {
        $data = $request->validate([
            'solution_id' => 'required|integer',
            'type' => 'required|in:classroom,teacher',
            'classroom_id' => 'nullable|integer',
            'grade_id' => 'nullable|integer',
            'teacher_id' => 'nullable|integer',
        ]);

        $solution = TimetableSolution::with('generation')->findOrFail($data['solution_id']);

        $schedule = YearlySchedule::where('academic_year_id', $solution->generation->academic_year_id)
            ->where('semester_id', $solution->generation->semester_id)
            ->first();

        $query = TimetableEntry::where('solution_id', $data['solution_id'])
            ->with('openedCourse.course', 'openedCourse.classroom', 'openedCourse.grade', 'teacher', 'room');

        if ($data['type'] === 'classroom') {
            $query->whereHas('openedCourse', fn($q) => $q->where('classroom_id', $data['classroom_id'])->where('grade_id', $data['grade_id']));
            $title = 'timetable_classroom';
        } else {
            $query->where('teacher_id', $data['teacher_id']);
            $title = 'timetable_teacher';
        }

        $entries = $query->get();

        // Build CSV
        $teachingDays = $schedule ? ($schedule->teaching_days ?? []) : [];
        $dayConfigs = $schedule ? ($schedule->day_configs ?? []) : [];
        $maxPeriods = collect($dayConfigs)->max('periods') ?? 0;

        $rows = [];
        $header = ['คาบ'];
        foreach ($teachingDays as $d) {
            $header[] = self::DAY_NAMES[(int)$d] ?? "วัน {$d}";
        }
        $rows[] = $header;

        for ($p = 1; $p <= $maxPeriods; $p++) {
            $row = ["คาบ {$p}"];
            foreach ($teachingDays as $d) {
                $entry = $entries->first(fn($e) => $e->day == (int)$d && $e->period == $p);
                if ($entry) {
                    $cell = ($entry->openedCourse->course->name ?? '');
                    $cell .= ' / ' . ($entry->teacher->name ?? '-');
                    $cell .= ' / ' . ($entry->room->room_number ?? '-');
                    $row[] = $cell;
                } else {
                    $row[] = '';
                }
            }
            $rows[] = $row;
        }

        // Convert to CSV with BOM for Excel UTF-8 support
        $csv = "\xEF\xBB\xBF";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn($cell) => '"' . str_replace('"', '""', $cell) . '"', $row)) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$title}.csv\"");
    }

    private function buildGridHtml(string $title, ?YearlySchedule $schedule, $entries, string $mode): string
    {
        $teachingDays = $schedule ? ($schedule->teaching_days ?? []) : [];
        $dayConfigs = $schedule ? ($schedule->day_configs ?? []) : [];
        $maxPeriods = collect($dayConfigs)->max('periods') ?? 0;

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . e($title) . '</title>';
        $html .= '<style>body{font-family:sans-serif;margin:20px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ddd;padding:8px;text-size:12px;text-align:center}th{background:#f5f5f5;font-weight:bold}.course{font-weight:bold;font-size:11px}.detail{font-size:10px;color:#666}@media print{body{margin:0}}</style>';
        $html .= '</head><body>';
        $html .= '<h2 style="text-align:center">' . e($title) . '</h2>';
        $html .= '<p style="text-align:center;color:#888;font-size:12px">พิมพ์: ' . now()->format('d/m/Y H:i') . '</p>';
        $html .= '<table><thead><tr><th>คาบ</th>';

        foreach ($teachingDays as $d) {
            $html .= '<th>' . (self::DAY_NAMES[(int)$d] ?? "วัน {$d}") . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        for ($p = 1; $p <= $maxPeriods; $p++) {
            $html .= "<tr><td><strong>คาบ {$p}</strong></td>";
            foreach ($teachingDays as $d) {
                $entry = $entries->first(fn($e) => $e->day == (int)$d && $e->period == $p);
                if ($entry) {
                    $courseName = e($entry->openedCourse->course->name ?? '');
                    $teacherName = e($entry->teacher->name ?? '-');
                    $roomNumber = e($entry->room->room_number ?? '-');
                    $extra = ($mode === 'teacher')
                        ? e(($entry->openedCourse->grade->name_th ?? '') . '/' . ($entry->openedCourse->classroom->name ?? ''))
                        : '';

                    $html .= "<td><div class=\"course\">{$courseName}</div><div class=\"detail\">{$teacherName}</div><div class=\"detail\">{$roomNumber}" . ($extra ? " | {$extra}" : '') . '</div></td>';
                } else {
                    $html .= '<td></td>';
                }
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '<script>window.print();</script>';
        $html .= '</body></html>';

        return $html;
    }
}
