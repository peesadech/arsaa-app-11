<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeSetting;
use App\Models\MasterOption;
use Illuminate\Http\Request;

class StudentMasterDataController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', MasterOption::TYPE_NATIONALITY);
        if (!in_array($type, MasterOption::TYPES) && $type !== 'grade_setting') {
            $type = MasterOption::TYPE_NATIONALITY;
        }

        $options = $type !== 'grade_setting'
            ? MasterOption::where('type', $type)->orderBy('sort_order')->orderBy('name_th')->get()
            : collect();

        $gradeSettings = GradeSetting::orderBy('sort_order')->get();

        return view('admin.student-master.index', compact('type', 'options', 'gradeSettings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:' . implode(',', MasterOption::TYPES),
            'name_th' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_cn' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:1,2',
        ]);

        MasterOption::create($data);

        return redirect()->route('admin.student-master.index', ['type' => $data['type']])
            ->with('status', __('created successfully!'));
    }

    public function update(Request $request, $id)
    {
        $option = MasterOption::findOrFail($id);

        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_cn' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:1,2',
        ]);

        $option->update($data);

        return redirect()->route('admin.student-master.index', ['type' => $option->type])
            ->with('status', __('updated successfully!'));
    }

    public function destroy($id)
    {
        $option = MasterOption::findOrFail($id);
        $type = $option->type;
        $option->delete();

        return redirect()->route('admin.student-master.index', ['type' => $type])
            ->with('status', __('deleted successfully!'));
    }

    public function storeGradeSetting(Request $request)
    {
        $data = $request->validate([
            'grade' => 'required|string|max:10',
            'min_score' => 'required|numeric|min:0|max:100',
            'max_score' => 'required|numeric|min:0|max:100|gte:min_score',
            'is_pass' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        GradeSetting::create($data);

        return redirect()->route('admin.student-master.index', ['type' => 'grade_setting'])
            ->with('status', __('created successfully!'));
    }

    public function updateGradeSetting(Request $request, $id)
    {
        $setting = GradeSetting::findOrFail($id);

        $data = $request->validate([
            'grade' => 'required|string|max:10',
            'min_score' => 'required|numeric|min:0|max:100',
            'max_score' => 'required|numeric|min:0|max:100|gte:min_score',
            'is_pass' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $setting->update($data);

        return redirect()->route('admin.student-master.index', ['type' => 'grade_setting'])
            ->with('status', __('updated successfully!'));
    }

    public function destroyGradeSetting($id)
    {
        GradeSetting::findOrFail($id)->delete();

        return redirect()->route('admin.student-master.index', ['type' => 'grade_setting'])
            ->with('status', __('deleted successfully!'));
    }
}
