<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CurrentAcademicSetting;
use App\Models\EducationLevel;
use App\Models\GlobalSchedule;
use App\Models\Semester;
use App\Models\YearlySchedule;
use Illuminate\Http\Request;

class YearlyScheduleController extends Controller
{
    private function resolveYearSemester(?int $academicYearId = null, ?int $semesterId = null): array
    {
        $academicYearId = $academicYearId ?? session('current_academic_year_id');
        $semesterId     = $semesterId ?? session('current_semester_id');

        if (! $academicYearId || ! $semesterId) {
            $global = CurrentAcademicSetting::latest()->first();
            $academicYearId = $global?->academic_year_id;
            $semesterId     = $global?->semester_id;
        }

        return [$academicYearId, $semesterId];
    }

    public function index()
    {
        [$academicYearId, $semesterId] = $this->resolveYearSemester();

        $academicYear = $academicYearId ? AcademicYear::find($academicYearId) : null;
        $semester     = $semesterId ? Semester::find($semesterId) : null;

        $educationLevels = EducationLevel::where('status', 1)->get();

        $scheduleMap = ($academicYearId && $semesterId)
            ? YearlySchedule::where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->get()
                ->keyBy('education_level_id')
            : collect();

        $globalScheduleMap = GlobalSchedule::whereNotNull('education_level_id')
            ->get()
            ->keyBy('education_level_id');

        return view('admin.yearly-schedule.index', compact(
            'educationLevels', 'scheduleMap', 'globalScheduleMap',
            'academicYear', 'semester', 'academicYearId', 'semesterId'
        ));
    }

    public function copyFromGlobal(Request $request)
    {
        $request->validate([
            'academic_year_id'    => 'required|exists:academic_years,id',
            'semester_id'         => 'required|exists:semesters,id',
            'education_level_id'  => 'required|exists:education_levels,id',
        ]);

        $global = GlobalSchedule::where('education_level_id', $request->education_level_id)->first();

        if (! $global) {
            return back()->with('error', 'ยังไม่มี Global Schedule สำหรับระดับการศึกษานี้');
        }

        YearlySchedule::updateOrCreate(
            [
                'academic_year_id'   => $request->academic_year_id,
                'semester_id'        => $request->semester_id,
                'education_level_id' => $request->education_level_id,
            ],
            [
                'teaching_days'   => $global->teaching_days,
                'start_time'      => $global->start_time,
                'period_duration' => $global->period_duration,
                'day_configs'     => $global->day_configs,
            ]
        );

        return redirect()->route('admin.yearly-schedule.index')
            ->with('success', 'คัดลอกกำหนดการจาก Global Schedule เรียบร้อยแล้ว');
    }

    public function copyAllFromGlobal(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id'      => 'required|exists:semesters,id',
        ]);

        $globals = GlobalSchedule::whereNotNull('education_level_id')->get();
        $count = 0;

        foreach ($globals as $global) {
            YearlySchedule::updateOrCreate(
                [
                    'academic_year_id'   => $request->academic_year_id,
                    'semester_id'        => $request->semester_id,
                    'education_level_id' => $global->education_level_id,
                ],
                [
                    'teaching_days'   => $global->teaching_days,
                    'start_time'      => $global->start_time,
                    'period_duration' => $global->period_duration,
                    'day_configs'     => $global->day_configs,
                ]
            );
            $count++;
        }

        return redirect()->route('admin.yearly-schedule.index')
            ->with('success', "คัดลอกกำหนดการทั้งหมด {$count} ระดับ จาก Global Schedule เรียบร้อยแล้ว");
    }

    public function edit($academicYearId, $semesterId, $educationLevelId)
    {
        $academicYear   = AcademicYear::findOrFail($academicYearId);
        $semester       = Semester::findOrFail($semesterId);
        $educationLevel = EducationLevel::findOrFail($educationLevelId);

        $schedule = YearlySchedule::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('education_level_id', $educationLevelId)
            ->first();

        if (! $schedule) {
            $defaultConfigs = [];
            foreach (['1','2','3','4','5'] as $d) {
                $defaultConfigs[$d] = ['periods' => 8, 'breaks' => []];
            }
            $schedule = new YearlySchedule([
                'academic_year_id'   => $academicYearId,
                'semester_id'        => $semesterId,
                'education_level_id' => $educationLevelId,
                'teaching_days'   => ['1','2','3','4','5'],
                'start_time'      => '08:00',
                'period_duration' => 50,
                'day_configs'     => $defaultConfigs,
            ]);
        }

        return view('admin.yearly-schedule.save', compact(
            'schedule', 'educationLevel', 'academicYear', 'semester'
        ));
    }

    public function update(Request $request, $academicYearId, $semesterId, $educationLevelId)
    {
        $academicYear   = AcademicYear::findOrFail($academicYearId);
        $semester       = Semester::findOrFail($semesterId);
        $educationLevel = EducationLevel::findOrFail($educationLevelId);

        $request->validate([
            'teaching_days'   => 'nullable|array',
            'teaching_days.*' => 'in:1,2,3,4,5,6,7',
            'start_time'      => 'required',
            'period_duration' => 'required|integer|min:1|max:240',
            'day_configs'     => 'nullable|string',
        ]);

        $dayConfigs = [];
        if ($request->filled('day_configs')) {
            $raw = json_decode($request->day_configs, true);
            if (is_array($raw)) {
                foreach ($raw as $dayNum => $config) {
                    $periods = max(0, min(20, (int)($config['periods'] ?? 0)));
                    $dayStartTime = null;
                    if (!empty($config['start_time'])) {
                        $t = \Carbon\Carbon::createFromFormat('H:i', substr($config['start_time'], 0, 5));
                        $dayStartTime = $t ? $t->format('H:i') : null;
                    }
                    $breaks = [];
                    foreach ($config['breaks'] ?? [] as $afterPeriod => $duration) {
                        $ap = (int) $afterPeriod;
                        $du = max(1, min(120, (int) $duration));
                        if ($ap >= 1 && $ap < $periods) {
                            $breaks[(string)$ap] = $du;
                        }
                    }
                    $dayConfigs[(string)$dayNum] = ['periods' => $periods, 'start_time' => $dayStartTime, 'breaks' => $breaks];
                }
            }
        }

        $schedule = YearlySchedule::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('education_level_id', $educationLevelId)
            ->first() ?? new YearlySchedule();

        $schedule->academic_year_id   = $academicYearId;
        $schedule->semester_id        = $semesterId;
        $schedule->education_level_id = $educationLevelId;
        $schedule->teaching_days   = $request->input('teaching_days', []);
        $schedule->start_time      = $request->start_time;
        $schedule->period_duration = $request->period_duration;
        $schedule->day_configs     = $dayConfigs;
        $schedule->save();

        return redirect()->route('admin.yearly-schedule.index')
            ->with('success', 'บันทึกกำหนดการ ' . $educationLevel->name_th . ' เรียบร้อยแล้ว');
    }
}
