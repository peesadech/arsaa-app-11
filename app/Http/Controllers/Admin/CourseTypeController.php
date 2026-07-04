<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseType;
use App\Models\GradingScheme;
use Illuminate\Http\Request;

class CourseTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseType::with('gradingScheme');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name_th', 'like', "%{$s}%")
                  ->orWhere('name_en', 'like', "%{$s}%");
            });
        }

        $sortBy = in_array($request->get('sort_by'), ['name_en', 'name_th', 'status', 'id'])
            ? $request->get('sort_by') : 'id';
        $sortOrder = $request->get('sort_order') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) $request->get('per_page', 10);
        $courseTypes = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.course-types._rows', compact('courseTypes'))->render(),
                'meta' => [
                    'total'        => $courseTypes->total(),
                    'per_page'     => $courseTypes->perPage(),
                    'current_page' => $courseTypes->currentPage(),
                    'last_page'    => $courseTypes->lastPage(),
                    'from'         => $courseTypes->firstItem() ?? 0,
                    'to'           => $courseTypes->lastItem() ?? 0,
                ],
            ]);
        }

        return view('admin.course-types.index', compact('courseTypes'));
    }

    public function create()
    {
        $gradingSchemes = GradingScheme::where('status', 1)->get();
        return view('admin.course-types.create', compact('gradingSchemes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grading_scheme_id' => 'nullable|exists:grading_schemes,id',
            'status' => 'required|in:1,2',
        ]);

        CourseType::create($data);

        return redirect()->route('admin.course-types.index')->with('status', __('Course type created successfully!'));
    }

    public function show($id)
    {
        $courseType = CourseType::with('gradingScheme')->findOrFail($id);
        return view('admin.course-types.show', compact('courseType'));
    }

    public function edit($id)
    {
        $courseType = CourseType::findOrFail($id);
        $gradingSchemes = GradingScheme::where('status', 1)->get();
        return view('admin.course-types.edit', compact('courseType', 'gradingSchemes'));
    }

    public function update(Request $request, $id)
    {
        $courseType = CourseType::findOrFail($id);
        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grading_scheme_id' => 'nullable|exists:grading_schemes,id',
            'status' => 'required|in:1,2',
        ]);

        $courseType->update($data);

        return redirect()->route('admin.course-types.index')->with('status', __('Course type updated successfully!'));
    }

    public function destroy($id)
    {
        $courseType = CourseType::findOrFail($id);
        $courseType->delete();
        return redirect()->route('admin.course-types.index')->with('status', __('Course type deleted successfully!'));
    }
}
