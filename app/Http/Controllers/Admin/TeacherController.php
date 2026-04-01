<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Course;
use App\Models\EducationLevel;
use App\Models\GlobalSchedule;
use App\Models\Grade;
use App\Models\SubjectGroup;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{
    public function index()
    {
        return view('admin.teachers.index');
    }

    public function data(Request $request)
    {
        $teachers = Teacher::with('courses.subjectGroup')->select('teachers.*');

        if ($request->filled('status')) {
            $teachers->where('teachers.status', $request->status);
        }

        return DataTables::of($teachers)
            ->addColumn('avatar', function ($teacher) {
                $path = $teacher->image_path ? asset($teacher->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode($teacher->name) . '&color=7F9CF5&background=EBF4FF';
                return '<img src="' . $path . '" class="w-10 h-10 rounded-xl object-cover shadow-sm border border-gray-100 dark:border-zinc-700" alt="Avatar">';
            })
            ->addColumn('courses_list', function ($teacher) {
                if ($teacher->courses->isEmpty()) {
                    return '<span class="text-gray-400 text-[10px] italic">No courses</span>';
                }
                $badges = '';
                foreach ($teacher->courses->take(3) as $course) {
                    $badges .= '<span class="inline-block px-2 py-0.5 mr-1 mb-1 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-bold">' . e($course->name) . '</span>';
                }
                if ($teacher->courses->count() > 3) {
                    $badges .= '<span class="inline-block px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-[10px] font-bold">+' . ($teacher->courses->count() - 3) . ' more</span>';
                }
                return $badges;
            })
            ->addColumn('status', function ($teacher) {
                $statusText = $teacher->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $teacher->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($teacher) {
                $editUrl = route('admin.teachers.edit', $teacher->id);
                $btn = '<div class="flex justify-end space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit Teacher"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $teacher->id . ', \'' . addslashes($teacher->name) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete Teacher"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['avatar', 'courses_list', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $subjectGroups = SubjectGroup::where('status', 1)->get();
        $semesters = Semester::all();
        $educationLevels = EducationLevel::where('status', 1)->get();
        return view('admin.teachers.save', compact('subjectGroups', 'semesters', 'educationLevels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:teachers',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'image_base64' => 'nullable|string',
            'status' => 'required|in:1,2',
            'courses' => 'nullable|array',
            'courses.*' => 'exists:courses,id',
            'unavailable_periods' => 'nullable|string',
        ]);

        $data['password'] = Hash::make($data['password']);

        if ($request->filled('unavailable_periods')) {
            $data['unavailable_periods'] = json_decode($request->input('unavailable_periods'), true);
        }

        if ($request->filled('image_base64')) {
            $data['image_path'] = $this->handleImageUpload($request->input('image_base64'));
        }

        $teacher = Teacher::create($data);

        if ($request->has('courses')) {
            $teacher->courses()->sync($request->input('courses'));
        }

        return redirect()->route('admin.teachers.index')->with('status', 'Teacher created successfully!');
    }

    public function edit($id)
    {
        $teacher = Teacher::with(['courses.grade.educationLevel'])->findOrFail($id);
        $subjectGroups = SubjectGroup::where('status', 1)->get();
        $semesters = Semester::all();
        $educationLevels = EducationLevel::where('status', 1)->get();
        $teacherCourseIds = $teacher->courses->pluck('id')->toArray();
        return view('admin.teachers.save', compact('teacher', 'subjectGroups', 'semesters', 'educationLevels', 'teacherCourseIds'));
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:teachers,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'image_base64' => 'nullable|string',
            'status' => 'required|in:1,2',
            'courses' => 'nullable|array',
            'courses.*' => 'exists:courses,id',
            'unavailable_periods' => 'nullable|string',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->filled('unavailable_periods')) {
            $data['unavailable_periods'] = json_decode($request->input('unavailable_periods'), true);
        } else {
            $data['unavailable_periods'] = null;
        }

        if ($request->filled('image_base64')) {
            if ($teacher->image_path) {
                $storagePath = str_replace('/storage/', '', $teacher->image_path);
                Storage::disk('public')->delete($storagePath);
            }
            $data['image_path'] = $this->handleImageUpload($request->input('image_base64'));
        }

        $teacher->update($data);
        $teacher->courses()->sync($request->input('courses', []));

        return redirect()->route('admin.teachers.index')->with('status', 'Teacher updated successfully!');
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);

        if ($teacher->image_path) {
            $storagePath = str_replace('/storage/', '', $teacher->image_path);
            Storage::disk('public')->delete($storagePath);
        }

        $teacher->delete();
        return redirect()->route('admin.teachers.index')->with('status', 'Teacher deleted successfully!');
    }

    public function searchCourses(Request $request)
    {
        $query = Course::with(['subjectGroup', 'semester', 'grade.educationLevel']);

        if ($request->filled('education_level_id')) {
            $query->whereHas('grade', function ($q) use ($request) {
                $q->where('education_level_id', $request->education_level_id);
            });
        }

        if ($request->filled('subject_group_id')) {
            $query->where('subject_group_id', $request->subject_group_id);
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $courses = $query->where('status', 1)->get();

        return response()->json($courses->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => $course->name,
                'grade' => $course->grade ? ($course->grade->name_th . ' / ' . $course->grade->name_en) : '-',
                'semester' => $course->semester ? $course->semester->semester_number : '-',
                'subject_group' => $course->subjectGroup ? $course->subjectGroup->name_th : '-',
                'education_level_id' => $course->grade?->education_level_id,
                'education_level_name' => $course->grade?->educationLevel?->name_th,
            ];
        }));
    }

    public function scheduleData(Request $request)
    {
        $educationLevelIds = $request->input('education_level_ids', []);
        if (empty($educationLevelIds)) {
            return response()->json([]);
        }

        $schedules = GlobalSchedule::whereIn('education_level_id', $educationLevelIds)
            ->with('educationLevel')
            ->get();

        return response()->json($schedules->map(function ($schedule) {
            return [
                'education_level_id' => $schedule->education_level_id,
                'education_level_name' => $schedule->educationLevel?->name_th ?? '-',
                'teaching_days' => $schedule->teaching_days ?? [],
                'start_time' => $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '08:00',
                'period_duration' => $schedule->period_duration ?? 50,
                'day_configs' => $schedule->day_configs ?? [],
            ];
        }));
    }

    private function handleImageUpload($base64Data)
    {
        $image_parts = explode(";base64,", $base64Data);
        $image_base64 = base64_decode($image_parts[1]);

        $fileName = time() . '_' . uniqid() . '.jpg';
        $path = 'image/teachers/' . $fileName;

        Storage::disk('public')->put($path, $image_base64);
        return '/storage/' . $path;
    }
}
