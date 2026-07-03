<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CurrentAcademicSetting;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\TeacherSubstitution;
use App\Models\YearlySchedule;
use App\Services\TeacherSubstitutionService;
use Illuminate\Http\Request;

class TeacherSubstitutionController extends Controller
{
    public function __construct(private TeacherSubstitutionService $service)
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

    public function show(Request $request, $teacherId)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        if (!$yearId || !$semesterId) {
            return redirect()->route('admin.teacher-term-status.index')
                ->with('error', __('Please select academic year and semester first'));
        }

        $teacher = Teacher::findOrFail($teacherId);
        $academicYear = AcademicYear::find($yearId);
        $semester = Semester::find($semesterId);
        $termStatus = $teacher->termStatus($yearId, $semesterId);

        $solution = $this->service->getActiveSolution($yearId, $semesterId);
        if (!$solution) {
            return redirect()->route('admin.teacher-term-status.edit', $teacherId)
                ->with('error', __('No active timetable for this term'));
        }

        $entries = $this->service->getAffectedEntries($solution->id, $teacherId);
        $candidates = $this->service->buildCandidates($entries, $yearId, $semesterId, $teacher->id);
        $history = $this->service->getHistory($solution->id, $teacherId);

        $yearlySchedules = YearlySchedule::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->with('educationLevel')
            ->get();

        return view('admin.teacher-substitution.show', compact(
            'teacher', 'academicYear', 'semester', 'termStatus',
            'solution', 'entries', 'candidates', 'history', 'yearlySchedules'
        ));
    }

    public function apply(Request $request, $teacherId)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $teacher = Teacher::findOrFail($teacherId);
        $solution = $this->service->getActiveSolution($yearId, $semesterId);

        if (!$solution) {
            return redirect()->route('admin.teacher-term-status.edit', $teacherId)
                ->with('error', __('No active timetable for this term'));
        }

        $data = $request->validate([
            'items' => 'required|array',
            'items.*' => ['required', 'string', 'regex:/^(keep|unassign|sub:\d+)$/'],
            'reason' => 'nullable|string|max:500',
        ]);

        $items = collect($data['items'])
            ->map(function ($choice, $entryId) {
                $isSub = str_starts_with($choice, 'sub:');
                return [
                    'entry_id' => (int) $entryId,
                    'action' => $isSub ? TeacherSubstitution::ACTION_SUBSTITUTE : $choice,
                    'to_teacher_id' => $isSub ? (int) substr($choice, 4) : null,
                ];
            })
            ->filter(fn($item) => $item['action'] !== 'keep')
            ->values()
            ->toArray();

        if (empty($items)) {
            return back()->with('error', __('No changes selected'));
        }

        $result = $this->service->apply($solution, $teacher, $items, $data['reason'] ?? null);

        $message = __(':count periods reassigned', ['count' => $result['applied']]);

        if (!empty($result['errors'])) {
            return back()
                ->with($result['applied'] > 0 ? 'status' : 'error', $message)
                ->with('substitution_errors', $result['errors']);
        }

        return redirect()->route('admin.teacher-substitution.show', $teacher->id)
            ->with('status', $message);
    }
}
