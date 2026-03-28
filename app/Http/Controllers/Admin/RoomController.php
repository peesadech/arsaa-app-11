<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Building;
use App\Models\Course;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoomController extends Controller
{
    public function index()
    {
        $buildings = Building::where('status', 1)->get();
        return view('admin.rooms.index', compact('buildings'));
    }

    public function data(Request $request)
    {
        $rooms = Room::with(['building', 'courses'])->select('rooms.*');

        if ($request->filled('status')) {
            $rooms->where('rooms.status', $request->status);
        }

        if ($request->filled('building_id')) {
            $rooms->where('rooms.building_id', $request->building_id);
        }

        return DataTables::of($rooms)
            ->addColumn('building_name', function ($room) {
                if (!$room->building) return '<span class="text-gray-400 text-[10px] italic">-</span>';
                return '<span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-[10px] font-bold">' . e($room->building->name_th) . '</span>';
            })
            ->addColumn('courses_list', function ($room) {
                if ($room->courses->isEmpty()) return '<span class="text-gray-400 text-[10px] italic">-</span>';
                $badges = $room->courses->map(function ($course) {
                    return '<span class="inline-block px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-bold mr-1 mb-1">' . e($course->name) . '</span>';
                })->implode('');
                return '<div class="flex flex-wrap">' . $badges . '</div>';
            })
            ->addColumn('status', function ($room) {
                $statusText = $room->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $room->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($room) {
                $editUrl = route('admin.rooms.edit', $room->id);
                $btn = '<div class="flex justify-end space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $room->id . ', \'' . addslashes($room->room_number) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['building_name', 'courses_list', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $buildings = Building::where('status', 1)->get();
        $courses = Course::where('status', 1)->get();
        return view('admin.rooms.save', compact('buildings', 'courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_number' => 'required|string|max:255',
            'building_id' => 'required|exists:buildings,id',
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        $room = Room::create($data);

        if (!empty($data['course_ids'])) {
            $room->courses()->sync($data['course_ids']);
        }

        return redirect()->route('admin.rooms.index')->with('status', 'Room created successfully!');
    }

    public function edit($id)
    {
        $room = Room::with('courses')->findOrFail($id);
        $buildings = Building::where('status', 1)->get();
        $courses = Course::where('status', 1)->get();
        return view('admin.rooms.save', compact('room', 'buildings', 'courses'));
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $data = $request->validate([
            'room_number' => 'required|string|max:255',
            'building_id' => 'required|exists:buildings,id',
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        $room->update($data);
        $room->courses()->sync($data['course_ids'] ?? []);

        return redirect()->route('admin.rooms.index')->with('status', 'Room updated successfully!');
    }

    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();
        return redirect()->route('admin.rooms.index')->with('status', 'Room deleted successfully!');
    }
}
