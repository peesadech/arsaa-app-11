<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalSchedule;
use Illuminate\Http\Request;

class GlobalScheduleController extends Controller
{
    public function index()
    {
        $schedule = GlobalSchedule::first();
        if (! $schedule) {
            $defaultConfigs = [];
            foreach (['1','2','3','4','5'] as $d) {
                $defaultConfigs[$d] = ['periods' => 8, 'breaks' => []];
            }
            $schedule = new GlobalSchedule([
                'teaching_days'   => ['1','2','3','4','5'],
                'start_time'      => '08:00',
                'period_duration' => 50,
                'day_configs'     => $defaultConfigs,
            ]);
        }

        return view('admin.global-schedule.index', compact('schedule'));
    }

    public function update(Request $request)
    {
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
                    $breaks  = [];
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

        $schedule = GlobalSchedule::first() ?? new GlobalSchedule();
        $schedule->teaching_days   = $request->input('teaching_days', []);
        $schedule->start_time      = $request->start_time;
        $schedule->period_duration = $request->period_duration;
        $schedule->day_configs     = $dayConfigs;
        $schedule->save();

        return redirect()->route('admin.global-schedule.index')
            ->with('success', 'บันทึกกำหนดการเรียนการสอนเรียบร้อยแล้ว');
    }
}
