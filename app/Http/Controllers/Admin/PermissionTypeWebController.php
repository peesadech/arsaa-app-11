<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PermissionTypeWebController extends Controller
{
    public function index()
    {
        return view('admin.permission-types.index');
    }

    public function data()
    {
        try {
            $query = PermissionType::query();
            return datatables()->of($query)
                ->addColumn('action', function ($row) {
                    $editUrl = route('admin.permission-types.edit', $row->permissionType_id);
                    $btn = '<div class="flex justify-end space-x-2">';
                    $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit"><i class="fas fa-edit text-xs"></i></a>';
                    $btn .= '<button type="button" onclick="confirmDelete(' . $row->permissionType_id . ', \'' . addslashes($row->permissionType_name) . '\')" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 text-rose-500 hover:border-rose-400 hover:bg-rose-50 transition-all duration-200 shadow-sm hover:shadow-md" title="Remove Category"><i class="fas fa-trash-alt text-sm"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create()
    {
        return view('admin.permission-types.save');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'permissionType_name' => 'required|string|unique:permission_types,permissionType_name',
            'image_base64'        => 'nullable|string',
        ]);

        if ($request->filled('image_base64')) {
            $base64Image  = $request->input('image_base64');
            $image_parts  = explode(";base64,", $base64Image);
            $image_base64 = base64_decode($image_parts[1]);

            $fileName = time() . '.jpg';
            $path = 'image/permissionTypes/' . $fileName;

            Storage::disk('public')->put($path, $image_base64);
            $data['permissionType_image_path'] = '/storage/' . $path;
        }

        unset($data['image_base64']);
        PermissionType::create($data);

        return redirect()->route('admin.permission-types')->with('status', 'Category created successfully!');
    }

    public function edit($id)
    {
        $permissionType = PermissionType::findOrFail($id);
        return view('admin.permission-types.save', compact('permissionType'));
    }

    public function update(Request $request, $id)
    {
        $permissionType = PermissionType::findOrFail($id);
        $data = $request->validate([
            'permissionType_name' => 'required|string|unique:permission_types,permissionType_name,' . $id . ',permissionType_id',
            'image_base64'        => 'nullable|string',
        ]);

        if ($request->filled('image_base64')) {
            $base64Image  = $request->input('image_base64');
            $image_parts  = explode(";base64,", $base64Image);
            $image_base64 = base64_decode($image_parts[1]);

            $fileName = time() . '.jpg';
            $path = 'image/permissionTypes/' . $fileName;

            if ($permissionType->permissionType_image_path) {
                $storagePath = str_replace('/storage/', '', $permissionType->permissionType_image_path);
                Storage::disk('public')->delete($storagePath);
            }

            Storage::disk('public')->put($path, $image_base64);
            $data['permissionType_image_path'] = '/storage/' . $path;
        }

        unset($data['image_base64']);
        $permissionType->update($data);

        return redirect()->route('admin.permission-types')->with('status', 'Category updated successfully!');
    }

    public function destroy($id)
    {
        $permissionType = PermissionType::findOrFail($id);
        $permissionType->delete();

        return redirect()->route('admin.permission-types')->with('status', 'Category removed successfully!');
    }
}
