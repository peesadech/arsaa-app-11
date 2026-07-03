<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CurrentAcademicSetting;
use App\Models\Semester;
use App\Services\TermSetupService;
use Illuminate\Http\Request;

class TermSetupController extends Controller
{
    public function __construct(private TermSetupService $service)
    {
    }

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

        if (!$yearId || !$semesterId) {
            return redirect()->route('admin.dashboard')
                ->with('error', __('Please select academic year and semester first'));
        }

        $academicYear = AcademicYear::find($yearId);
        $semester = Semester::find($semesterId);
        $readiness = $this->service->readiness($yearId, $semesterId);
        $sourceTerms = $this->service->listSourceTerms($yearId, $semesterId);

        // สำหรับตัวเลือกเทอมปลายทาง (เทอมใหม่ที่จะตั้งค่า)
        $allYears = AcademicYear::orderByDesc('year')->get();
        $allSemesters = Semester::where('status', 1)->orderBy('semester_number')->get();

        // เทอมที่มีข้อมูลแล้ว — ห้ามสร้าง/clone ซ้ำ
        $existingTermKeys = $this->service->termKeysWithData();
        $termHasData = in_array($yearId . '-' . $semesterId, $existingTermKeys);

        return view('admin.term-setup.index', compact(
            'academicYear', 'semester', 'yearId', 'semesterId', 'readiness', 'sourceTerms',
            'allYears', 'allSemesters', 'existingTermKeys', 'termHasData'
        ));
    }

    public function existing(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $existingTerms = $this->service->listSourceTerms(0, 0);

        return view('admin.term-setup.existing', compact('existingTerms', 'yearId', 'semesterId'));
    }

    public function cloneFromTerm(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        if (!$yearId || !$semesterId) {
            return back()->with('error', __('Please select academic year and semester first'));
        }

        $data = $request->validate([
            'source_term' => 'required|string|regex:/^\d+-\d+$/',
            'parts' => 'required|array|min:1',
            'parts.*' => 'in:schedules,opened,teachers',
        ]);

        [$fromYearId, $fromSemesterId] = array_map('intval', explode('-', $data['source_term']));

        if ($fromYearId === (int) $yearId && $fromSemesterId === (int) $semesterId) {
            return back()->with('error', __('Source term must be different from the current term'));
        }

        // ห้าม clone ทับเทอมที่มีข้อมูลอยู่แล้ว
        if ($this->service->hasData((int) $yearId, (int) $semesterId)) {
            return back()->with('error', __('This term already has data — cannot create a duplicate. Please select a new term.'));
        }

        $result = $this->service->cloneFromTerm($fromYearId, $fromSemesterId, $yearId, $semesterId, $data['parts']);

        $summary = collect([
            'schedules' => __('Schedules'),
            'grades' => __('Grade Levels'),
            'classrooms' => __('Classrooms'),
            'courses' => __('Courses'),
            'teacher_statuses' => __('Teacher statuses'),
            'teacher_courses' => __('Teacher courses'),
        ])
            ->filter(fn($label, $key) => $result[$key] > 0)
            ->map(fn($label, $key) => "{$label}: {$result[$key]}")
            ->join(', ');

        return redirect()->route('admin.term-setup.index')
            ->with('status', $summary !== ''
                ? __('Cloned successfully') . " — {$summary}"
                : __('Nothing new to clone — data already exists'));
    }
}
