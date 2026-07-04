<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradingScheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class GradingSchemeController extends Controller
{
    public function index(Request $request)
    {
        $query = GradingScheme::withCount('details');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('name', 'like', "%{$s}%");
        }

        $sortBy = in_array($request->get('sort_by'), ['name', 'status', 'id'])
            ? $request->get('sort_by') : 'id';
        $sortOrder = $request->get('sort_order') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) $request->get('per_page', 10);
        $schemes = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.grading-schemes._rows', compact('schemes'))->render(),
                'meta' => [
                    'total'        => $schemes->total(),
                    'per_page'     => $schemes->perPage(),
                    'current_page' => $schemes->currentPage(),
                    'last_page'    => $schemes->lastPage(),
                    'from'         => $schemes->firstItem() ?? 0,
                    'to'           => $schemes->lastItem() ?? 0,
                ],
            ]);
        }

        return view('admin.grading-schemes.index', compact('schemes'));
    }

    public function data(Request $request)
    {
        $schemes = GradingScheme::withCount('details')->with('details')->select('grading_schemes.*');

        if ($request->filled('status')) {
            $schemes->where('status', $request->status);
        }

        return DataTables::of($schemes)
            ->addColumn('result_type_badge', function ($scheme) {
                $isGrade = $scheme->result_type === GradingScheme::RESULT_TYPE_GRADE;
                $text = $isGrade ? __('Grade (A-F)') : __('Pass / Fail');
                $color = $isGrade ? 'bg-indigo-50 text-indigo-600' : 'bg-purple-50 text-purple-600';
                return '<span class="px-2 py-1 rounded-lg ' . $color . ' text-[10px] font-bold uppercase tracking-wider">' . $text . '</span>';
            })
            ->addColumn('details_count', function ($scheme) {
                if ($scheme->details->isEmpty()) {
                    return '<span class="text-gray-400 text-[10px] italic">-</span>';
                }
                $badges = '';
                foreach ($scheme->details as $d) {
                    $badges .= '<span class="inline-block px-2 py-0.5 mr-1 mb-1 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-bold">' . e($d->condition_text) . ' → ' . e($d->result) . '</span>';
                }
                return $badges;
            })
            ->addColumn('status', function ($scheme) {
                $statusText = $scheme->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $scheme->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($scheme) {
                $editUrl = route('admin.grading-schemes.edit', $scheme->id);
                $btn = '<div class="flex justify-end space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $scheme->id . ', \'' . addslashes($scheme->name) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['result_type_badge', 'details_count', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.grading-schemes.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->validateRanges($request);

        DB::transaction(function () use ($data) {
            $scheme = GradingScheme::create($data);
            $this->syncDetails($scheme, $data['details'] ?? []);
        });

        return redirect()->route('admin.grading-schemes.index')->with('status', __('created successfully!'));
    }

    public function edit($id)
    {
        $gradingScheme = GradingScheme::with('details')->findOrFail($id);
        return view('admin.grading-schemes.edit', compact('gradingScheme'));
    }

    public function update(Request $request, $id)
    {
        $gradingScheme = GradingScheme::findOrFail($id);
        $data = $this->validateData($request);
        $this->validateRanges($request);

        DB::transaction(function () use ($gradingScheme, $data) {
            $gradingScheme->update($data);
            $this->syncDetails($gradingScheme, $data['details'] ?? []);
        });

        return redirect()->route('admin.grading-schemes.index')->with('status', __('updated successfully!'));
    }

    public function destroy($id)
    {
        $gradingScheme = GradingScheme::findOrFail($id);
        $gradingScheme->delete();
        return redirect()->route('admin.grading-schemes.index')->with('status', __('deleted successfully!'));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'result_type' => 'required|in:' . implode(',', GradingScheme::RESULT_TYPES),
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
            'details' => 'required|array|min:1',
            'details.*.min_score' => 'required|numeric|min:0|max:100',
            'details.*.max_score' => 'required|numeric|min:0|max:100|gte:details.*.min_score',
            'details.*.result_th' => 'required|string|max:50',
            'details.*.result_en' => 'nullable|string|max:50',
            'details.*.result_cn' => 'nullable|string|max:50',
            'details.*.description' => 'nullable|string|max:255',
        ], [
            'details.required' => __('At least one grade row is required'),
            'details.min' => __('At least one grade row is required'),
            'details.*.result_th.required' => __('Result (TH) is required'),
            'details.*.max_score.gte' => __('Max score must not be less than min score'),
        ]);
    }

    /**
     * ตรวจช่วงคะแนน: ห้ามซ้อนทับ + ห้ามมีช่องว่างถ้าครอบคลุม 0-100
     */
    private function validateRanges(Request $request): void
    {
        $rows = collect($request->input('details', []))
            ->filter(fn($d) => ($d['min_score'] ?? '') !== '' && ($d['max_score'] ?? '') !== '')
            ->map(fn($d) => ['min' => (float) $d['min_score'], 'max' => (float) $d['max_score']])
            ->sortBy('min')
            ->values();

        if ($rows->isEmpty()) return;

        $errors = [];

        // ซ้อนทับ: ช่วงถัดไปต้องเริ่มมากกว่าเพดานช่วงก่อนหน้า
        for ($i = 1; $i < $rows->count(); $i++) {
            if ($rows[$i]['min'] <= $rows[$i - 1]['max']) {
                $errors[] = __('Score ranges must not overlap (e.g. :a and :b)', [
                    'a' => $rows[$i - 1]['min'] . '-' . $rows[$i - 1]['max'],
                    'b' => $rows[$i]['min'] . '-' . $rows[$i]['max'],
                ]);
                break;
            }
        }

        // ช่องว่าง: ถ้าครอบคลุมตั้งแต่ 0 ถึง 100 ห้ามมีช่องโหว่ระหว่างช่วง (เผื่อขอบ .99/.00 = 1.0)
        $overallMin = $rows->min('min');
        $overallMax = $rows->max('max');
        if ($overallMin <= 0 && $overallMax >= 100) {
            for ($i = 1; $i < $rows->count(); $i++) {
                if ($rows[$i]['min'] - $rows[$i - 1]['max'] > 1) {
                    $errors[] = __('There must be no gap between score ranges (found between :a and :b)', [
                        'a' => $rows[$i - 1]['max'],
                        'b' => $rows[$i]['min'],
                    ]);
                    break;
                }
            }
        }

        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages(['details' => $errors]);
        }
    }

    private function syncDetails(GradingScheme $scheme, array $details): void
    {
        $scheme->details()->delete();

        foreach (array_values($details) as $i => $detail) {
            if (($detail['min_score'] ?? '') === '' || empty($detail['result_th'])) continue;

            $scheme->details()->create([
                'min_score' => $detail['min_score'],
                'max_score' => $detail['max_score'],
                'result_th' => $detail['result_th'],
                'result_en' => $detail['result_en'] ?? null,
                'result_cn' => $detail['result_cn'] ?? null,
                'description' => $detail['description'] ?? null,
                'sort_order' => $i + 1,
            ]);
        }
    }
}
