<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubjectGroup;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SubjectGroupController extends Controller
{
    public function index(Request $request)
    {
        $query = SubjectGroup::query();

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
        $subjectGroups = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.subject-groups._rows', compact('subjectGroups'))->render(),
                'meta' => [
                    'total'        => $subjectGroups->total(),
                    'per_page'     => $subjectGroups->perPage(),
                    'current_page' => $subjectGroups->currentPage(),
                    'last_page'    => $subjectGroups->lastPage(),
                    'from'         => $subjectGroups->firstItem() ?? 0,
                    'to'           => $subjectGroups->lastItem() ?? 0,
                ],
            ]);
        }

        return view('admin.subject-groups.index', compact('subjectGroups'));
    }

    public function data(Request $request)
    {
        $groups = SubjectGroup::select('subject_groups.*');

        if ($request->filled('status')) {
            $groups->where('status', $request->status);
        }

        return DataTables::of($groups)
            ->addColumn('status', function ($group) {
                $statusText = $group->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $group->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($group) {
                $editUrl = route('admin.subject-groups.edit', $group->id);
                $btn = '<div class="flex justify-end space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $group->id . ', \'' . addslashes($group->name_th) . ' / ' . addslashes($group->name_en) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $teachers = \App\Models\Teacher::where('status', 1)->orderBy('name')->get(['id', 'name']);
        return view('admin.subject-groups.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'head_teacher_id' => 'nullable|exists:teachers,id',
            'status' => 'required|in:1,2',
        ]);

        SubjectGroup::create($data);

        return redirect()->route('admin.subject-groups.index')->with('status', 'Subject Group created successfully!');
    }

    public function edit($id)
    {
        $subjectGroup = SubjectGroup::findOrFail($id);
        $teachers = \App\Models\Teacher::where('status', 1)->orderBy('name')->get(['id', 'name']);
        return view('admin.subject-groups.edit', compact('subjectGroup', 'teachers'));
    }

    public function update(Request $request, $id)
    {
        $subjectGroup = SubjectGroup::findOrFail($id);
        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'head_teacher_id' => 'nullable|exists:teachers,id',
            'status' => 'required|in:1,2',
        ]);

        $subjectGroup->update($data);

        return redirect()->route('admin.subject-groups.index')->with('status', 'Subject Group updated successfully!');
    }

    public function destroy($id)
    {
        $subjectGroup = SubjectGroup::findOrFail($id);
        $subjectGroup->delete();
        return redirect()->route('admin.subject-groups.index')->with('status', 'Subject Group deleted successfully!');
    }
}
