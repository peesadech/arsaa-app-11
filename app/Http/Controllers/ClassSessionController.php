<?php

namespace App\Http\Controllers;

use App\Models\AttendanceStatus;
use App\Models\ClassSession;
use App\Models\CurrentAcademicSetting;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Services\ClassSessionService;
use App\Services\TeacherAccountService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassSessionController extends Controller
{
    public function __construct(
        private ClassSessionService $sessions,
        private TeacherAccountService $accountService,
    ) {
    }

    /** [user, isAdmin, teacher|null] */
    private function context(): array
    {
        $user = Auth::user();
        $isAdmin = $user && $user->getRoleNames()
            ->map(fn ($r) => strtoupper($r))->intersect(['ADMIN', 'SUPERADMIN'])->isNotEmpty();
        $teacher = $this->accountService->teacherForUser($user);

        return [$user, $isAdmin, $teacher];
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

    /** ตารางสอนของวันนี้ (ครู = ของตัวเอง, admin = ทั้งหมด/กรองตามครูได้) พร้อมปุ่มเปิดคาบ */
    public function today(Request $request)
    {
        [$user, $isAdmin, $teacher] = $this->context();
        abort_unless($isAdmin || $teacher, 403, __('No teacher record linked to this account'));

        [$yearId, $semesterId] = $this->resolveYearSemester($request);
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        // ครูเห็นเฉพาะของตัวเอง; admin เลือกกรองตามครูได้
        $filterTeacherId = $teacher?->id;
        if ($isAdmin && !$teacher) {
            $filterTeacherId = $request->filled('teacher_id') ? (int) $request->teacher_id : null;
        } elseif ($isAdmin && $request->filled('teacher_id')) {
            $filterTeacherId = (int) $request->teacher_id;
        }

        $schedule = ($yearId && $semesterId)
            ? $this->sessions->scheduleFor($yearId, $semesterId, $date, $filterTeacherId)
            : collect();

        $teachers = $isAdmin ? Teacher::where('status', 1)->orderBy('name')->get() : collect();

        return view('class-sessions.today', [
            'schedule'        => $schedule,
            'date'            => $date,
            'isAdmin'         => $isAdmin,
            'teachers'        => $teachers,
            'filterTeacherId' => $filterTeacherId,
            'hasTerm'         => (bool) ($yearId && $semesterId),
        ]);
    }

    /** เปิด/สร้าง session ของ timetable entry ในวันหนึ่ง แล้วเข้าหน้า session */
    public function open(Request $request)
    {
        [$user, $isAdmin, $teacher] = $this->context();

        $data = $request->validate([
            'timetable_entry_id' => 'required|exists:timetable_entries,id',
            'date'               => 'nullable|date',
        ]);

        $entry = TimetableEntry::with('openedCourse.grade')->findOrFail($data['timetable_entry_id']);
        $this->authorizeTeacher($isAdmin, $teacher, $entry->teacher_id);

        $date = !empty($data['date']) ? Carbon::parse($data['date']) : Carbon::today();
        $session = $this->sessions->openOrCreate($entry, $date, $user?->id);

        return redirect()->route('class-sessions.show', $session->id);
    }

    /** หน้า session — แท็บ Attendance (แท็บอื่นเตรียมโครงสร้างไว้) */
    public function show(Request $request, $id)
    {
        [$user, $isAdmin, $teacher] = $this->context();

        $session = ClassSession::with([
            'course.subjectGroup', 'grade', 'classroom', 'teacher', 'academicYear', 'semester',
        ])->findOrFail($id);

        \Illuminate\Support\Facades\Gate::authorize('manage', $session);

        $students = $this->sessions->studentsFor($session);
        $records = $session->students()->get()->keyBy('student_id');
        $statuses = AttendanceStatus::active();
        $teachingLog = $session->teachingLog;
        $homeworks = $session->homeworks;
        $assessments = $session->assessments;
        $files = $session->files->where('kind', \App\Models\ClassSessionFile::KIND_FILE)->values();
        $photos = $session->files->where('kind', \App\Models\ClassSessionFile::KIND_PHOTO)->values();

        return view('class-sessions.show', compact('session', 'students', 'records', 'statuses', 'isAdmin', 'teachingLog', 'homeworks', 'assessments', 'files', 'photos'));
    }

    /** บันทึกการเข้าเรียน */
    public function saveAttendance(Request $request, $id)
    {
        [$user, $isAdmin, $teacher] = $this->context();
        $session = ClassSession::findOrFail($id);
        \Illuminate\Support\Facades\Gate::authorize('manage', $session);

        $rows = $request->input('attendance', []);

        // บังคับกรอกหมายเหตุสำหรับสถานะที่ require_remark
        $requireIds = AttendanceStatus::where('is_require_remark', true)->pluck('id')->map(fn ($i) => (int) $i)->all();
        foreach ($rows as $row) {
            $stId = ($row['attendance_status_id'] ?? '') !== '' ? (int) $row['attendance_status_id'] : null;
            if ($stId && in_array($stId, $requireIds, true) && trim((string) ($row['remark'] ?? '')) === '') {
                return back()->with('error', __('Please fill in the remark for statuses that require it.'));
            }
        }

        $saved = $this->sessions->saveAttendance($session, $rows, $user?->id);

        return redirect()->route('class-sessions.show', $session->id)
            ->with('status', __(':count attendance records saved', ['count' => $saved]));
    }

    /** บันทึกบันทึกการสอน (Teaching Log) ของคาบ */
    public function saveTeachingLog(Request $request, $id)
    {
        [$user, $isAdmin, $teacher] = $this->context();
        $session = ClassSession::findOrFail($id);
        \Illuminate\Support\Facades\Gate::authorize('manage', $session);

        $data = $request->validate([
            'topic'         => 'nullable|string|max:255',
            'content'       => 'nullable|string',
            'notes'         => 'nullable|string',
            'problems'      => 'nullable|string',
            'assigned_work' => 'nullable|string',
        ]);

        \App\Models\ClassSessionTeachingLog::updateOrCreate(
            ['class_session_id' => $session->id],
            array_merge($data, ['created_by' => $user?->id])
        );

        return redirect()->route('class-sessions.show', $session->id)
            ->with('status', __('Teaching log saved.'));
    }

    /** เพิ่มการบ้าน (Homework) ให้คาบ */
    public function storeHomework(Request $request, $id)
    {
        [$user, $isAdmin, $teacher] = $this->context();
        $session = ClassSession::findOrFail($id);
        \Illuminate\Support\Facades\Gate::authorize('manage', $session);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'max_score'   => 'nullable|numeric|min:0|max:9999',
        ]);

        $session->homeworks()->create(array_merge($data, ['created_by' => $user?->id]));

        return redirect()->route('class-sessions.show', $session->id)
            ->with('status', __('Homework added.'));
    }

    /** ลบการบ้าน */
    public function deleteHomework(Request $request, $id, $homeworkId)
    {
        [$user, $isAdmin, $teacher] = $this->context();
        $session = ClassSession::findOrFail($id);
        \Illuminate\Support\Facades\Gate::authorize('manage', $session);

        \App\Models\ClassSessionHomework::where('class_session_id', $session->id)->where('id', $homeworkId)->delete();

        return redirect()->route('class-sessions.show', $session->id)
            ->with('status', __('Homework deleted.'));
    }

    /** เพิ่มรายการประเมิน (Assessment) */
    public function storeAssessment(Request $request, $id)
    {
        [$user, $isAdmin, $teacher] = $this->context();
        $session = ClassSession::findOrFail($id);
        \Illuminate\Support\Facades\Gate::authorize('manage', $session);

        $data = $request->validate([
            'type'        => 'required|in:' . implode(',', \App\Models\ClassSessionAssessment::TYPES),
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_score'   => 'nullable|numeric|min:0|max:9999',
        ]);

        $session->assessments()->create(array_merge($data, ['created_by' => $user?->id]));

        return redirect()->route('class-sessions.show', $session->id)
            ->with('status', __('Assessment added.'));
    }

    /** ลบรายการประเมิน */
    public function deleteAssessment(Request $request, $id, $assessmentId)
    {
        [$user, $isAdmin, $teacher] = $this->context();
        $session = ClassSession::findOrFail($id);
        \Illuminate\Support\Facades\Gate::authorize('manage', $session);

        \App\Models\ClassSessionAssessment::where('class_session_id', $session->id)->where('id', $assessmentId)->delete();

        return redirect()->route('class-sessions.show', $session->id)
            ->with('status', __('Assessment deleted.'));
    }

    /** อัปโหลดไฟล์/รูปของคาบ */
    public function uploadFile(Request $request, $id)
    {
        [$user, $isAdmin, $teacher] = $this->context();
        $session = ClassSession::findOrFail($id);
        \Illuminate\Support\Facades\Gate::authorize('manage', $session);

        $kind = $request->input('kind') === \App\Models\ClassSessionFile::KIND_PHOTO
            ? \App\Models\ClassSessionFile::KIND_PHOTO
            : \App\Models\ClassSessionFile::KIND_FILE;

        $request->validate([
            'file' => $kind === \App\Models\ClassSessionFile::KIND_PHOTO
                ? 'required|image|max:10240'
                : 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store("class-sessions/{$session->id}", 'public');

        $session->files()->create([
            'kind'          => $kind,
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime'          => $file->getClientMimeType(),
            'size'          => $file->getSize(),
            'created_by'    => $user?->id,
        ]);

        return redirect()->route('class-sessions.show', $session->id)
            ->with('status', $kind === \App\Models\ClassSessionFile::KIND_PHOTO ? __('Photo uploaded.') : __('File uploaded.'));
    }

    /** ลบไฟล์/รูป */
    public function deleteFile(Request $request, $id, $fileId)
    {
        [$user, $isAdmin, $teacher] = $this->context();
        $session = ClassSession::findOrFail($id);
        \Illuminate\Support\Facades\Gate::authorize('manage', $session);

        $file = \App\Models\ClassSessionFile::where('class_session_id', $session->id)->where('id', $fileId)->first();
        if ($file) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($file->path);
            $file->delete();
        }

        return redirect()->route('class-sessions.show', $session->id)->with('status', __('File deleted.'));
    }

    /** เปลี่ยนสถานะคาบ (OPEN/CLOSED/CANCELLED/POSTPONED) */
    public function updateStatus(Request $request, $id)
    {
        [$user, $isAdmin, $teacher] = $this->context();
        $session = ClassSession::findOrFail($id);
        \Illuminate\Support\Facades\Gate::authorize('manage', $session);

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', ClassSession::STATUSES),
            'remark' => 'nullable|string|max:500',
        ]);

        $session->update($data);

        return back()->with('status', __('Session status updated.'));
    }

    private function authorizeTeacher(bool $isAdmin, ?Teacher $teacher, ?int $ownerTeacherId): void
    {
        if ($isAdmin) {
            return;
        }
        abort_unless($teacher && $teacher->id === $ownerTeacherId, 403, __('You can only manage your own class sessions'));
    }
}
