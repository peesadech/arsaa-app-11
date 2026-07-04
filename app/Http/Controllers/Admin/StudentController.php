<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\MasterOption;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAddress;
use App\Models\StudentDocument;
use App\Models\StudentEducationHistory;
use App\Models\StudentGuardian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with([
            'enrollments' => function ($q) {
                $q->where('status', 'enrolled')
                    ->with(['grade', 'classroom', 'academicYear', 'semester'])
                    ->orderByDesc('id');
            },
        ]);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('student_code', 'like', "%{$s}%")
                    ->orWhere('name_th', 'like', "%{$s}%")
                    ->orWhere('name_cn', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('mobile', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('students.status', $request->status);
        }

        if ($request->filled('academic_year_id') || $request->filled('semester_id') || $request->filled('classroom_id')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('status', 'enrolled');
                if ($request->filled('academic_year_id')) $q->where('academic_year_id', $request->academic_year_id);
                if ($request->filled('semester_id')) $q->where('semester_id', $request->semester_id);
                if ($request->filled('classroom_id')) $q->where('classroom_id', $request->classroom_id);
            });
        }

        $sortBy = in_array($request->get('sort_by'), ['student_code', 'name_th', 'status', 'id'])
            ? $request->get('sort_by') : 'id';
        $sortOrder = $request->get('sort_order') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) $request->get('per_page', 10);
        $students = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.students._rows', compact('students'))->render(),
                'meta' => [
                    'total'        => $students->total(),
                    'per_page'     => $students->perPage(),
                    'current_page' => $students->currentPage(),
                    'last_page'    => $students->lastPage(),
                    'from'         => $students->firstItem() ?? 0,
                    'to'           => $students->lastItem() ?? 0,
                ],
            ]);
        }

        $academicYears = AcademicYear::orderByDesc('year')->get();
        $semesters = Semester::where('status', 1)->get();
        $classrooms = Classroom::where('status', 1)->get();

        return view('admin.students.index', compact('students', 'academicYears', 'semesters', 'classrooms'));
    }

    public function data(Request $request)
    {
        $students = Student::with('nationality')->select('students.*');

        // ค้นหา: รหัส, ชื่อไทย, ชื่อจีน, เบอร์โทร
        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $students->where(function ($q) use ($kw) {
                $q->where('student_code', 'like', "%{$kw}%")
                    ->orWhere('name_th', 'like', "%{$kw}%")
                    ->orWhere('name_cn', 'like', "%{$kw}%")
                    ->orWhere('phone', 'like', "%{$kw}%")
                    ->orWhere('mobile', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('status')) {
            $students->where('students.status', $request->status);
        }

        // Filter ตามปี/เทอม/ห้อง ผ่าน enrollment
        if ($request->filled('academic_year_id') || $request->filled('semester_id') || $request->filled('classroom_id')) {
            $students->whereHas('enrollments', function ($q) use ($request) {
                $q->where('status', 'enrolled');
                if ($request->filled('academic_year_id')) $q->where('academic_year_id', $request->academic_year_id);
                if ($request->filled('semester_id')) $q->where('semester_id', $request->semester_id);
                if ($request->filled('classroom_id')) $q->where('classroom_id', $request->classroom_id);
            });
        }

        return DataTables::of($students)
            ->addColumn('avatar', function ($student) {
                $path = $student->image_path ? asset($student->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode($student->name_th) . '&color=7F9CF5&background=EBF4FF';
                return '<img src="' . $path . '" class="w-10 h-10 rounded-xl object-cover shadow-sm border border-gray-100 dark:border-zinc-700" alt="">';
            })
            ->addColumn('name_display', function ($student) {
                $cn = $student->name_cn ? '<div class="text-[11px] text-gray-400">' . e($student->name_cn) . '</div>' : '';
                return '<div class="font-bold text-sm">' . e($student->name_th) . '</div>' . $cn;
            })
            ->addColumn('status_badge', function ($student) {
                $colors = [
                    'studying' => 'bg-emerald-50 text-emerald-600',
                    'suspended' => 'bg-amber-50 text-amber-600',
                    'resigned' => 'bg-rose-50 text-rose-600',
                    'graduated' => 'bg-indigo-50 text-indigo-600',
                ];
                $labels = [
                    'studying' => __('Studying'),
                    'suspended' => __('Suspended'),
                    'resigned' => __('Resigned'),
                    'graduated' => __('Graduated'),
                ];
                $color = $colors[$student->status] ?? 'bg-gray-50 text-gray-500';
                return '<span class="px-2 py-1 rounded-lg ' . $color . ' text-[10px] font-bold uppercase tracking-wider">' . ($labels[$student->status] ?? $student->status) . '</span>';
            })
            ->addColumn('current_classroom', function ($student) {
                $enrollment = $student->enrollments()
                    ->where('status', 'enrolled')
                    ->with('grade', 'classroom', 'academicYear', 'semester')
                    ->orderByDesc('id')
                    ->first();
                if (!$enrollment) return '<span class="text-gray-300 text-xs">-</span>';
                return '<span class="text-xs">' . e(($enrollment->grade->name_th ?? '') . ' / ' . ($enrollment->classroom->name ?? ''))
                    . '</span><div class="text-[10px] text-gray-400">' . e(($enrollment->academicYear->year ?? '') . ' / ' . ($enrollment->semester->semester_number ?? '')) . '</div>';
            })
            ->addColumn('action', function ($student) {
                $editUrl = route('admin.students.edit', $student->id);
                $profileUrl = route('admin.student-reports.profile', $student->id);
                $btn = '<div class="flex justify-end space-x-2">';
                $btn .= '<a href="' . $profileUrl . '" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-indigo-500 hover:bg-indigo-50 transition-all shadow-sm" title="' . __('Student Profile') . '"><i class="fas fa-id-card text-xs"></i></a>';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all shadow-sm" title="' . __('Edit') . '"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $student->id . ', \'' . addslashes($student->name_th) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all shadow-sm" title="' . __('Delete') . '"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['avatar', 'name_display', 'status_badge', 'current_classroom', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.students.create', $this->formData());
    }

    public function store(StudentRequest $request)
    {
        $student = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['student_code'] = $data['student_code'] ?: Student::generateCode();
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            if ($request->filled('image_base64')) {
                $data['image_path'] = $this->handleImageUpload($request->input('image_base64'));
            }

            $student = Student::create(collect($data)->except(['addresses', 'guardians', 'educations', 'documents', 'document_files', 'primary_guardian', 'image_base64'])->toArray());

            $this->syncRelations($student, $request);

            return $student;
        });

        return redirect()->route('admin.students.edit', $student->id)
            ->with('status', __('created successfully!'));
    }

    public function edit($id)
    {
        $student = Student::with([
            'addresses', 'guardians', 'educationHistories', 'documents',
            'enrollments.academicYear', 'enrollments.semester', 'enrollments.grade', 'enrollments.classroom',
            'scores.openedCourse.course', 'scores.openedCourse.academicYear', 'scores.openedCourse.semester',
        ])->findOrFail($id);

        return view('admin.students.edit', array_merge($this->formData(), compact('student')));
    }

    public function update(StudentRequest $request, $id)
    {
        $student = Student::findOrFail($id);

        DB::transaction(function () use ($request, $student) {
            $data = $request->validated();
            $data['student_code'] = $data['student_code'] ?: $student->student_code;
            $data['updated_by'] = Auth::id();

            if ($request->filled('image_base64')) {
                if ($student->image_path) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $student->image_path));
                }
                $data['image_path'] = $this->handleImageUpload($request->input('image_base64'));
            }

            $student->update(collect($data)->except(['addresses', 'guardians', 'educations', 'documents', 'document_files', 'primary_guardian', 'image_base64'])->toArray());

            $this->syncRelations($student, $request);
        });

        return redirect()->route('admin.students.edit', $student->id)
            ->with('status', __('updated successfully!'));
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        if ($student->image_path) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $student->image_path));
        }

        foreach ($student->documents as $doc) {
            if ($doc->file_path) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $doc->file_path));
            }
        }

        $student->delete();

        return redirect()->route('admin.students.index')->with('status', __('deleted successfully!'));
    }

    private function formData(): array
    {
        return [
            'nationalities' => MasterOption::options(MasterOption::TYPE_NATIONALITY),
            'religions' => MasterOption::options(MasterOption::TYPE_RELIGION),
            'bloodTypes' => MasterOption::options(MasterOption::TYPE_BLOOD_TYPE),
            'guardianTypes' => MasterOption::options(MasterOption::TYPE_GUARDIAN_TYPE),
            'documentTypes' => MasterOption::options(MasterOption::TYPE_DOCUMENT_TYPE),
            'provinces' => MasterOption::options(MasterOption::TYPE_PROVINCE),
        ];
    }

    private function syncRelations(Student $student, Request $request): void
    {
        // ที่อยู่: current / registered
        foreach ([StudentAddress::TYPE_CURRENT, StudentAddress::TYPE_REGISTERED] as $type) {
            $addr = $request->input("addresses.{$type}", []);
            if (array_filter($addr)) {
                StudentAddress::updateOrCreate(
                    ['student_id' => $student->id, 'type' => $type],
                    collect($addr)->only(['house_no', 'moo', 'subdistrict', 'district', 'province_id', 'postal_code'])->toArray()
                );
            } else {
                StudentAddress::where('student_id', $student->id)->where('type', $type)->delete();
            }
        }

        // ผู้ปกครอง: ลบแล้วสร้างใหม่ตามฟอร์ม
        $student->guardians()->delete();
        $primaryIndex = (int) $request->input('primary_guardian', 0);
        foreach ($request->input('guardians', []) as $i => $guardian) {
            if (empty($guardian['name'])) continue;
            $student->guardians()->create(array_merge(
                collect($guardian)->only([
                    'guardian_type_id', 'name', 'name_cn', 'age', 'race_id', 'nationality_id',
                    'religion_id', 'living_status', 'address', 'phone', 'occupation',
                    'workplace_address', 'relationship',
                ])->toArray(),
                ['is_primary' => (int) $i === $primaryIndex]
            ));
        }

        // ประวัติการศึกษา
        $student->educationHistories()->delete();
        foreach ($request->input('educations', []) as $edu) {
            if (empty($edu['school_name'])) continue;
            $student->educationHistories()->create(
                collect($edu)->only(['school_name', 'school_location', 'last_level', 'gpa', 'graduated_at', 'note'])->toArray()
            );
        }

        // เอกสาร checklist (keyed by document_type_id)
        foreach ($request->input('documents', []) as $typeId => $doc) {
            $record = StudentDocument::firstOrNew([
                'student_id' => $student->id,
                'document_type_id' => $typeId,
            ]);

            $record->is_received = !empty($doc['is_received']);
            $record->note = $doc['note'] ?? null;

            if ($request->hasFile("document_files.{$typeId}")) {
                if ($record->file_path) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $record->file_path));
                }
                $path = $request->file("document_files.{$typeId}")->store('documents/students/' . $student->id, 'public');
                $record->file_path = '/storage/' . $path;
            }

            $record->save();
        }
    }

    private function handleImageUpload($base64Data)
    {
        $image_parts = explode(";base64,", $base64Data);
        $image_base64 = base64_decode($image_parts[1]);

        $fileName = time() . '_' . uniqid() . '.jpg';
        $path = 'image/students/' . $fileName;

        Storage::disk('public')->put($path, $image_base64);
        return '/storage/' . $path;
    }
}
