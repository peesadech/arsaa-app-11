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
    private function dayNames(): array
    {
        return [
            1 => __('Monday'), 2 => __('Tuesday'), 3 => __('Wednesday'),
            4 => __('Thursday'), 5 => __('Friday'), 6 => __('Saturday'), 7 => __('Sunday'),
        ];
    }

    public function exportClassroomPdf(Request $request)
    {
        $data = $request->validate([
            'solution_id' => 'required|integer',
            'classroom_id' => 'required|integer',
            'grade_id' => 'required|integer',
            'layout' => 'nullable|in:normal,transposed',
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

        $title = __('Timetable') . " {$grade->name_th} / {$classroom->name}";
        $layout = $data['layout'] ?? 'normal';
        $html = $this->buildGridHtml($title, $schedule, $entries, 'classroom', $layout);

        return response($html)->header('Content-Type', 'text/html; charset=utf-8');
    }

    public function exportTeacherPdf(Request $request)
    {
        $data = $request->validate([
            'solution_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'layout' => 'nullable|in:normal,transposed',
        ]);

        $solution = TimetableSolution::with('generation')->findOrFail($data['solution_id']);
        $teacher = Teacher::findOrFail($data['teacher_id']);

        $entries = TimetableEntry::where('solution_id', $data['solution_id'])
            ->where('teacher_id', $data['teacher_id'])
            ->with('openedCourse.course', 'openedCourse.classroom', 'openedCourse.grade.educationLevel', 'room')
            ->get();

        $schedule = $this->mergeSchedulesForEntries($solution, $entries);

        $title = __('Teaching Schedule') . " — {$teacher->name}";
        $layout = $data['layout'] ?? 'normal';
        $html = $this->buildGridHtml($title, $schedule, $entries, 'teacher', $layout);

        return response($html)->header('Content-Type', 'text/html; charset=utf-8');
    }

    public function exportRoomPdf(Request $request)
    {
        $data = $request->validate([
            'solution_id' => 'required|integer',
            'room_id' => 'required|integer',
            'layout' => 'nullable|in:normal,transposed',
        ]);

        $solution = TimetableSolution::with('generation')->findOrFail($data['solution_id']);
        $room = Room::with('building')->findOrFail($data['room_id']);

        $entries = TimetableEntry::where('solution_id', $data['solution_id'])
            ->where('room_id', $data['room_id'])
            ->with('openedCourse.course', 'openedCourse.classroom', 'openedCourse.grade.educationLevel', 'teacher')
            ->get();

        $schedule = $this->mergeSchedulesForEntries($solution, $entries);

        $roomLabel = $room->room_number . ($room->building ? " ({$room->building->name_th})" : '');
        $title = __('Room Schedule') . " {$roomLabel}";
        $layout = $data['layout'] ?? 'normal';
        $html = $this->buildGridHtml($title, $schedule, $entries, 'room', $layout);

        return response($html)->header('Content-Type', 'text/html; charset=utf-8');
    }

    public function exportExcel(Request $request)
    {
        $data = $request->validate([
            'solution_id' => 'required|integer',
            'type' => 'required|in:classroom,teacher,room',
            'classroom_id' => 'nullable|integer',
            'grade_id' => 'nullable|integer',
            'teacher_id' => 'nullable|integer',
            'room_id' => 'nullable|integer',
            'layout' => 'nullable|in:normal,transposed',
        ]);

        $solution = TimetableSolution::with('generation')->findOrFail($data['solution_id']);
        $dayNames = $this->dayNames();
        $layout = $data['layout'] ?? 'normal';

        $query = TimetableEntry::where('solution_id', $data['solution_id'])
            ->with('openedCourse.course', 'openedCourse.classroom', 'openedCourse.grade.educationLevel', 'teacher', 'room');

        if ($data['type'] === 'classroom') {
            $query->whereHas('openedCourse', fn($q) => $q->where('classroom_id', $data['classroom_id'])->where('grade_id', $data['grade_id']));
            $grade = Grade::with('educationLevel')->findOrFail($data['grade_id']);
            $schedule = YearlySchedule::where('academic_year_id', $solution->generation->academic_year_id)
                ->where('semester_id', $solution->generation->semester_id)
                ->where('education_level_id', $grade->education_level_id)
                ->first();
            $title = 'timetable_classroom';
        } elseif ($data['type'] === 'teacher') {
            $query->where('teacher_id', $data['teacher_id']);
            $title = 'timetable_teacher';
            $schedule = null; // resolved after fetching entries
        } else {
            $query->where('room_id', $data['room_id']);
            $title = 'timetable_room';
            $schedule = null; // resolved after fetching entries
        }

        $entries = $query->get();

        // For teacher/room: merge schedules from all education levels
        if ($data['type'] !== 'classroom') {
            $schedule = $this->mergeSchedulesForEntries($solution, $entries);
        }

        $dayConfigs = $schedule ? ($schedule->day_configs ?? []) : [];
        $teachingDays = $this->filterTeachingDays($schedule ? ($schedule->teaching_days ?? []) : [], $dayConfigs);
        $maxPeriods = collect($dayConfigs)->max('periods') ?? 0;
        $periodTimes = $this->calcPeriodTimes($schedule);

        $rows = [];

        if ($layout === 'transposed') {
            // Header: Day | Period 1 (time) | Period 2 (time) | ...
            $header = [__('Day')];
            for ($p = 1; $p <= $maxPeriods; $p++) {
                $label = __('Period') . " {$p}";
                if (isset($periodTimes[$p])) $label .= " ({$periodTimes[$p]})";
                $header[] = $label;
            }
            $rows[] = $header;

            // Rows: one per day
            foreach ($teachingDays as $d) {
                $row = [$dayNames[(int)$d] ?? __('Day') . " {$d}"];
                $dc = $dayConfigs[$d] ?? [];
                $dayPeriods = $dc['periods'] ?? 0;
                for ($p = 1; $p <= $maxPeriods; $p++) {
                    if ($p > $dayPeriods) {
                        $row[] = '';
                        continue;
                    }
                    $entry = $entries->first(fn($e) => $e->day == (int)$d && $e->period == $p);
                    $row[] = $entry ? $this->formatEntryCsv($entry, $data['type']) : '';
                }
                $rows[] = $row;
            }
        } else {
            // Normal: Header = Period | Day1 | Day2 | ...
            $header = [__('Period')];
            foreach ($teachingDays as $d) {
                $header[] = $dayNames[(int)$d] ?? __('Day') . " {$d}";
            }
            $rows[] = $header;

            for ($p = 1; $p <= $maxPeriods; $p++) {
                $periodLabel = __('Period') . " {$p}";
                if (isset($periodTimes[$p])) $periodLabel .= " ({$periodTimes[$p]})";
                $row = [$periodLabel];
                foreach ($teachingDays as $d) {
                    $entry = $entries->first(fn($e) => $e->day == (int)$d && $e->period == $p);
                    $row[] = $entry ? $this->formatEntryCsv($entry, $data['type']) : '';
                }
                $rows[] = $row;
            }
        }

        $csv = "\xEF\xBB\xBF";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn($cell) => '"' . str_replace('"', '""', $cell) . '"', $row)) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$title}.csv\"");
    }

    public function exportWord(Request $request)
    {
        $data = $request->validate([
            'solution_id' => 'required|integer',
            'type' => 'required|in:classroom,teacher,room',
            'classroom_id' => 'nullable|integer',
            'grade_id' => 'nullable|integer',
            'teacher_id' => 'nullable|integer',
            'room_id' => 'nullable|integer',
            'layout' => 'nullable|in:normal,transposed',
        ]);

        $solution = TimetableSolution::with('generation')->findOrFail($data['solution_id']);
        $layout = $data['layout'] ?? 'normal';

        $query = TimetableEntry::where('solution_id', $data['solution_id'])
            ->with('openedCourse.course', 'openedCourse.classroom', 'openedCourse.grade.educationLevel', 'teacher', 'room');

        $schedule = null;
        if ($data['type'] === 'classroom') {
            $query->whereHas('openedCourse', fn($q) => $q->where('classroom_id', $data['classroom_id'])->where('grade_id', $data['grade_id']));
            $grade = Grade::with('educationLevel')->findOrFail($data['grade_id']);
            $classroom = Classroom::findOrFail($data['classroom_id']);
            $schedule = YearlySchedule::where('academic_year_id', $solution->generation->academic_year_id)
                ->where('semester_id', $solution->generation->semester_id)
                ->where('education_level_id', $grade->education_level_id)
                ->first();
            $title = __('Timetable') . " {$grade->name_th} / {$classroom->name}";
            $filename = "timetable_{$grade->name_th}_{$classroom->name}.doc";
        } elseif ($data['type'] === 'teacher') {
            $query->where('teacher_id', $data['teacher_id']);
            $teacher = Teacher::findOrFail($data['teacher_id']);
            $title = __('Teaching Schedule') . " — {$teacher->name}";
            $filename = "timetable_teacher_{$teacher->name}.doc";
        } else {
            $query->where('room_id', $data['room_id']);
            $room = Room::with('building')->findOrFail($data['room_id']);
            $roomLabel = $room->room_number . ($room->building ? " ({$room->building->name_th})" : '');
            $title = __('Room Schedule') . " {$roomLabel}";
            $filename = "timetable_room_{$room->room_number}.doc";
        }

        $entries = $query->get();

        // For teacher/room: merge schedules from all education levels
        if ($data['type'] !== 'classroom') {
            $schedule = $this->mergeSchedulesForEntries($solution, $entries);
        }

        $html = $this->buildWordHtml($title, $schedule, $entries, $data['type'], $layout);

        return response($html)
            ->header('Content-Type', 'application/msword; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    // ==================== Grid builders ====================

    private function buildGridHtml(string $title, ?YearlySchedule $schedule, $entries, string $mode, string $layout = 'normal'): string
    {
        $preamble = $this->htmlPreamble($title);
        $table = $layout === 'transposed'
            ? $this->buildTableTransposed($schedule, $entries, $mode, 'html')
            : $this->buildTableNormal($schedule, $entries, $mode, 'html');

        return $preamble . $table . '<script>window.print();</script></body></html>';
    }

    private function buildWordHtml(string $title, ?YearlySchedule $schedule, $entries, string $mode, string $layout = 'normal'): string
    {
        $preamble = $this->wordPreamble($title);
        $table = $layout === 'transposed'
            ? $this->buildTableTransposed($schedule, $entries, $mode, 'word')
            : $this->buildTableNormal($schedule, $entries, $mode, 'word');

        return $preamble . $table . '</body></html>';
    }

    private function htmlPreamble(string $title): string
    {
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . e($title) . '</title>';
        $html .= '<style>body{font-family:sans-serif;margin:20px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ddd;padding:8px;font-size:12px;text-align:center}th{background:#f5f5f5;font-weight:bold}.course{font-weight:bold;font-size:11px}.detail{font-size:10px;color:#666}.break-row{background:#fffbeb;font-size:10px;color:#d97706}.time{font-size:9px;color:#999;font-weight:normal}@media print{body{margin:0}}</style>';
        $html .= '</head><body>';
        $html .= '<h2 style="text-align:center">' . e($title) . '</h2>';
        $html .= '<p style="text-align:center;color:#888;font-size:12px">' . __('Printed') . ': ' . now()->format('d/m/Y H:i') . '</p>';
        return $html;
    }

    private function wordPreamble(string $title): string
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta charset="utf-8"><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $html .= '<style>body{font-family:"TH SarabunPSK","Sarabun",sans-serif;font-size:14pt}table{border-collapse:collapse;width:100%}th,td{border:1px solid #000;padding:4px 6px;text-align:center;vertical-align:middle}th{background:#e5e7eb;font-weight:bold}.course{font-weight:bold}.detail{font-size:12pt;color:#555}.break-row{background:#fef3c7;font-size:12pt}.time{font-size:10pt;color:#888;font-weight:normal}</style>';
        $html .= '</head><body>';
        $html .= '<h2 style="text-align:center">' . e($title) . '</h2>';
        $html .= '<p style="text-align:center;color:#888;font-size:11pt">' . __('Printed') . ': ' . now()->format('d/m/Y H:i') . '</p>';
        return $html;
    }

    // ---- Normal: columns=days, rows=periods ----
    private function buildTableNormal(?YearlySchedule $schedule, $entries, string $mode, string $format): string
    {
        $dayNames = $this->dayNames();
        $dayConfigs = $schedule ? ($schedule->day_configs ?? []) : [];
        $teachingDays = $this->filterTeachingDays($schedule ? ($schedule->teaching_days ?? []) : [], $dayConfigs);
        $maxPeriods = collect($dayConfigs)->max('periods') ?? 0;
        $periodTimes = $this->calcPeriodTimes($schedule);

        $html = '<table><thead><tr><th>' . __('Period') . '</th>';
        foreach ($teachingDays as $d) {
            $html .= '<th>' . ($dayNames[(int)$d] ?? __('Day') . " {$d}") . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        for ($p = 1; $p <= $maxPeriods; $p++) {
            $timeLabel = isset($periodTimes[$p]) ? '<br><span class="time">' . $periodTimes[$p] . '</span>' : '';
            $html .= '<tr><td><strong>' . __('Period') . " {$p}</strong>{$timeLabel}</td>";
            foreach ($teachingDays as $d) {
                $dc = $dayConfigs[$d] ?? [];
                if ($p > ($dc['periods'] ?? 0)) {
                    $html .= '<td style="background:#f3f4f6"></td>';
                } else {
                    $entry = $entries->first(fn($e) => $e->day == (int)$d && $e->period == $p);
                    $html .= $entry ? '<td>' . $this->formatEntryHtml($entry, $mode) . '</td>' : '<td></td>';
                }
            }
            $html .= '</tr>';

            $breaks = $dayConfigs[$teachingDays[0] ?? '1']['breaks'] ?? [];
            if (isset($breaks[(string)$p]) && $breaks[(string)$p] > 0) {
                $html .= '<tr><td colspan="' . (count($teachingDays) + 1) . '" class="break-row">';
                $html .= __('Break') . ' ' . $breaks[(string)$p] . ' ' . __('minutes');
                $html .= '</td></tr>';
            }
        }

        $html .= '</tbody></table>';
        return $html;
    }

    // ---- Transposed: columns=periods, rows=days ----
    private function buildTableTransposed(?YearlySchedule $schedule, $entries, string $mode, string $format): string
    {
        $dayNames = $this->dayNames();
        $dayConfigs = $schedule ? ($schedule->day_configs ?? []) : [];
        $teachingDays = $this->filterTeachingDays($schedule ? ($schedule->teaching_days ?? []) : [], $dayConfigs);
        $maxPeriods = collect($dayConfigs)->max('periods') ?? 0;
        $periodTimes = $this->calcPeriodTimes($schedule);

        // Header: Day | Period 1 | Period 2 | ...
        $html = '<table><thead><tr><th>' . __('Day') . '</th>';
        for ($p = 1; $p <= $maxPeriods; $p++) {
            $timeLabel = isset($periodTimes[$p]) ? '<br><span class="time">' . $periodTimes[$p] . '</span>' : '';
            $html .= '<th>' . __('Period') . " {$p}{$timeLabel}</th>";
        }
        $html .= '</tr></thead><tbody>';

        // Rows: one per teaching day
        foreach ($teachingDays as $d) {
            $dc = $dayConfigs[$d] ?? [];
            $dayPeriods = $dc['periods'] ?? 0;
            $html .= '<tr><td><strong>' . ($dayNames[(int)$d] ?? __('Day') . " {$d}") . '</strong></td>';
            for ($p = 1; $p <= $maxPeriods; $p++) {
                if ($p > $dayPeriods) {
                    $html .= '<td style="background:#f3f4f6"></td>';
                } else {
                    $entry = $entries->first(fn($e) => $e->day == (int)$d && $e->period == $p);
                    $html .= $entry ? '<td>' . $this->formatEntryHtml($entry, $mode) . '</td>' : '<td></td>';
                }
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    // ==================== Helpers ====================

    /**
     * For teacher/room exports: merge YearlySchedules from all education levels
     * that the entries belong to. Returns a virtual YearlySchedule with merged
     * teaching_days (union) and day_configs (max periods per day).
     */
    private function mergeSchedulesForEntries(TimetableSolution $solution, $entries): ?YearlySchedule
    {
        $eduLevelIds = $entries
            ->map(fn($e) => $e->openedCourse->grade->education_level_id ?? null)
            ->filter()
            ->unique()
            ->values();

        if ($eduLevelIds->isEmpty()) return null;

        $schedules = YearlySchedule::where('academic_year_id', $solution->generation->academic_year_id)
            ->where('semester_id', $solution->generation->semester_id)
            ->whereIn('education_level_id', $eduLevelIds)
            ->get();

        if ($schedules->isEmpty()) return null;
        if ($schedules->count() === 1) return $schedules->first();

        // Merge: union teaching_days, merge day_configs using max periods
        $mergedTeachingDays = collect();
        $mergedDayConfigs = [];
        $periodDuration = $schedules->first()->period_duration ?? 50;
        $startTime = $schedules->first()->start_time ?? '08:00';

        foreach ($schedules as $sch) {
            foreach (($sch->teaching_days ?? []) as $d) {
                $dk = (string)$d;
                $mergedTeachingDays->push($dk);
                $dc = ($sch->day_configs ?? [])[$dk] ?? null;
                if ($dc) {
                    if (!isset($mergedDayConfigs[$dk]) || ($dc['periods'] ?? 0) > ($mergedDayConfigs[$dk]['periods'] ?? 0)) {
                        $mergedDayConfigs[$dk] = $dc;
                    }
                }
            }
        }

        $merged = new YearlySchedule();
        $allDays = $mergedTeachingDays->unique()->sort()->values()->all();
        $merged->teaching_days = $this->filterTeachingDays($allDays, $mergedDayConfigs);
        $merged->day_configs = $mergedDayConfigs;
        $merged->period_duration = $periodDuration;
        $merged->start_time = $startTime;

        return $merged;
    }

    /**
     * Filter teaching_days: only keep days that have periods > 0 in day_configs.
     * Fixes legacy data where non-teaching days were incorrectly saved.
     */
    private function filterTeachingDays(array $teachingDays, array $dayConfigs): array
    {
        return array_values(array_filter($teachingDays, function ($d) use ($dayConfigs) {
            $dc = $dayConfigs[(string)$d] ?? $dayConfigs[(int)$d] ?? null;
            return $dc && (($dc['periods'] ?? 0) > 0);
        }));
    }

    private function calcPeriodTimes(?YearlySchedule $schedule): array
    {
        if (!$schedule) return [];

        $teachingDays = $schedule->teaching_days ?? [];
        $dayConfigs = $schedule->day_configs ?? [];
        $periodDuration = $schedule->period_duration ?? 50;
        $globalStart = $schedule->start_time ?? '08:00';

        $firstDay = $teachingDays[0] ?? null;
        if (!$firstDay) return [];

        $dc = $dayConfigs[$firstDay] ?? [];
        $startTime = $dc['start_time'] ?? $globalStart;
        $breaks = $dc['breaks'] ?? [];
        $periods = $dc['periods'] ?? 0;

        $minutes = $this->timeToMinutes($startTime);
        $times = [];

        for ($p = 1; $p <= $periods; $p++) {
            $start = $this->minutesToTime($minutes);
            $end = $this->minutesToTime($minutes + $periodDuration);
            $times[$p] = "{$start}-{$end}";
            $minutes += $periodDuration;
            if (isset($breaks[(string)$p])) {
                $minutes += (int)$breaks[(string)$p];
            }
        }

        return $times;
    }

    private function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);
        return (int)$parts[0] * 60 + (int)($parts[1] ?? 0);
    }

    private function minutesToTime(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    private function formatEntryHtml($entry, string $mode): string
    {
        $courseName = e($entry->openedCourse->course->name ?? '');
        $teacherName = e($entry->teacher->name ?? '-');
        $roomNumber = e($entry->room->room_number ?? '-');

        $extra = '';
        if ($mode === 'teacher' || $mode === 'room') {
            $extra = e(($entry->openedCourse->grade->name_th ?? '') . '/' . ($entry->openedCourse->classroom->name ?? ''));
        }

        $html = "<div class=\"course\">{$courseName}</div>";
        $html .= "<div class=\"detail\">{$teacherName}</div>";
        $html .= "<div class=\"detail\">{$roomNumber}" . ($extra ? " | {$extra}" : '') . '</div>';

        return $html;
    }

    private function formatEntryCsv($entry, string $type): string
    {
        $cell = ($entry->openedCourse->course->name ?? '');
        $cell .= ' / ' . ($entry->teacher->name ?? '-');
        $cell .= ' / ' . ($entry->room->room_number ?? '-');
        if ($type === 'teacher' || $type === 'room') {
            $cell .= ' | ' . ($entry->openedCourse->grade->name_th ?? '') . '/' . ($entry->openedCourse->classroom->name ?? '');
        }
        return $cell;
    }
}
