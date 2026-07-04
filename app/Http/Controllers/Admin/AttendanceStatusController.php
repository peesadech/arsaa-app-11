<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceStatus;
use Illuminate\Http\Request;

class AttendanceStatusController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceStatus::query();

        if ($request->filled('is_active') && in_array($request->is_active, ['1', '0'], true)) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                  ->orWhere('name_th', 'like', "%{$s}%")
                  ->orWhere('name_en', 'like', "%{$s}%");
            });
        }

        $sortBy = in_array($request->get('sort_by'), ['sort_order', 'code', 'name_th', 'id'])
            ? $request->get('sort_by') : 'sort_order';
        $sortOrder = $request->get('sort_order') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) $request->get('per_page', 10);
        $statuses = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.attendance-statuses._rows', compact('statuses'))->render(),
                'meta' => [
                    'total'        => $statuses->total(),
                    'per_page'     => $statuses->perPage(),
                    'current_page' => $statuses->currentPage(),
                    'last_page'    => $statuses->lastPage(),
                    'from'         => $statuses->firstItem() ?? 0,
                    'to'           => $statuses->lastItem() ?? 0,
                ],
            ]);
        }

        return view('admin.attendance-statuses.index', compact('statuses'));
    }

    public function create()
    {
        return view('admin.attendance-statuses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'                => 'required|string|max:50|unique:attendance_statuses,code',
            'name_th'             => 'required|string|max:255',
            'name_en'             => 'nullable|string|max:255',
            'status_type'         => 'nullable|string|max:50',
            'is_count_as_present' => 'boolean',
            'is_count_as_absent'  => 'boolean',
            'is_late'             => 'boolean',
            'is_leave'            => 'boolean',
            'is_require_remark'   => 'boolean',
            'color'               => 'nullable|string|max:20',
            'sort_order'          => 'nullable|integer',
            'is_active'           => 'boolean',
        ]);

        AttendanceStatus::create([
            'code'                => $request->code,
            'name_th'             => $request->name_th,
            'name_en'             => $request->name_en,
            'status_type'         => $request->status_type,
            'is_count_as_present' => $request->boolean('is_count_as_present'),
            'is_count_as_absent'  => $request->boolean('is_count_as_absent'),
            'is_late'             => $request->boolean('is_late'),
            'is_leave'            => $request->boolean('is_leave'),
            'is_require_remark'   => $request->boolean('is_require_remark'),
            'color'               => $request->color,
            'sort_order'          => (int) $request->get('sort_order', 0),
            'is_active'           => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.attendance-statuses.index')->with('status', __('Attendance status created successfully!'));
    }

    public function edit($id)
    {
        $status = AttendanceStatus::findOrFail($id);
        return view('admin.attendance-statuses.edit', compact('status'));
    }

    public function update(Request $request, $id)
    {
        $status = AttendanceStatus::findOrFail($id);

        $request->validate([
            'code'                => 'required|string|max:50|unique:attendance_statuses,code,' . $status->id,
            'name_th'             => 'required|string|max:255',
            'name_en'             => 'nullable|string|max:255',
            'status_type'         => 'nullable|string|max:50',
            'is_count_as_present' => 'boolean',
            'is_count_as_absent'  => 'boolean',
            'is_late'             => 'boolean',
            'is_leave'            => 'boolean',
            'is_require_remark'   => 'boolean',
            'color'               => 'nullable|string|max:20',
            'sort_order'          => 'nullable|integer',
            'is_active'           => 'boolean',
        ]);

        $status->update([
            'code'                => $request->code,
            'name_th'             => $request->name_th,
            'name_en'             => $request->name_en,
            'status_type'         => $request->status_type,
            'is_count_as_present' => $request->boolean('is_count_as_present'),
            'is_count_as_absent'  => $request->boolean('is_count_as_absent'),
            'is_late'             => $request->boolean('is_late'),
            'is_leave'            => $request->boolean('is_leave'),
            'is_require_remark'   => $request->boolean('is_require_remark'),
            'color'               => $request->color,
            'sort_order'          => (int) $request->get('sort_order', 0),
            'is_active'           => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.attendance-statuses.index')->with('status', __('Attendance status updated successfully!'));
    }

    public function destroy($id)
    {
        $status = AttendanceStatus::findOrFail($id);
        $status->delete();
        return redirect()->route('admin.attendance-statuses.index')->with('status', __('Attendance status deleted successfully!'));
    }
}
