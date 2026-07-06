<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BehaviorScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BehaviorScoreController extends Controller
{
    private function assertType(string $type): void
    {
        abort_unless(in_array($type, BehaviorScore::TYPES, true), 404);
    }

    /** กฎ validate คะแนน: ความดี > 0, ความชั่ว < 0 */
    private function scoreRule(string $type): string
    {
        return $type === BehaviorScore::TYPE_MERIT
            ? 'required|numeric|gt:0|max:100'
            : 'required|numeric|lt:0|min:-100';
    }

    public function index(Request $request, string $type)
    {
        $this->assertType($type);

        $query = BehaviorScore::type($type);

        if ($request->filled('is_active') && in_array($request->is_active, ['1', '0'], true)) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('name', 'like', "%{$s}%");
        }

        $sortBy = in_array($request->get('sort_by'), ['sort_order', 'name', 'score', 'id'])
            ? $request->get('sort_by') : 'sort_order';
        $sortOrder = $request->get('sort_order') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) $request->get('per_page', 10);
        $items = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.behavior-scores._rows', compact('items', 'type'))->render(),
                'meta' => [
                    'total'        => $items->total(),
                    'per_page'     => $items->perPage(),
                    'current_page' => $items->currentPage(),
                    'last_page'    => $items->lastPage(),
                    'from'         => $items->firstItem() ?? 0,
                    'to'           => $items->lastItem() ?? 0,
                ],
            ]);
        }

        // รายการทั้งหมด (เรียงตามลำดับ) สำหรับโหมดจัดลำดับด้วยการลาก
        $allItems = BehaviorScore::type($type)->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.behavior-scores.index', compact('items', 'allItems', 'type'));
    }

    public function create(string $type)
    {
        $this->assertType($type);

        return view('admin.behavior-scores.create', compact('type'));
    }

    public function store(Request $request, string $type)
    {
        $this->assertType($type);

        $request->validate([
            'name'      => 'required|string|max:150',
            'score'     => $this->scoreRule($type),
            'is_active' => 'boolean',
        ]);

        // ลำดับใหม่ต่อท้ายอันสุดท้าย +1
        $nextSort = (int) BehaviorScore::type($type)->max('sort_order') + 1;

        BehaviorScore::create([
            'type'       => $type,
            'name'       => $request->name,
            'score'      => $request->score,
            'sort_order' => $nextSort,
            'is_active'  => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.behavior-scores.index', $type)
            ->with('status', __('Saved successfully'));
    }

    public function edit(string $type, $id)
    {
        $this->assertType($type);

        $item = BehaviorScore::type($type)->findOrFail($id);

        return view('admin.behavior-scores.edit', compact('item', 'type'));
    }

    public function update(Request $request, string $type, $id)
    {
        $this->assertType($type);

        $item = BehaviorScore::type($type)->findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:150',
            'score'     => $this->scoreRule($type),
            'is_active' => 'boolean',
        ]);

        // ไม่แตะ sort_order (จัดลำดับผ่านหน้า index)
        $item->update([
            'name'      => $request->name,
            'score'     => $request->score,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.behavior-scores.index', $type)
            ->with('status', __('Saved successfully'));
    }

    /** บันทึกลำดับใหม่จากการลาก (JSON) */
    public function reorder(Request $request, string $type)
    {
        $this->assertType($type);

        $data = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer',
        ]);

        $validIds = BehaviorScore::type($type)->pluck('id')->all();
        DB::transaction(function () use ($data, $validIds) {
            $sort = 1;
            foreach ($data['order'] as $id) {
                if (in_array((int) $id, $validIds, true)) {
                    BehaviorScore::where('id', $id)->update(['sort_order' => $sort++]);
                }
            }
        });

        return response()->json(['ok' => true]);
    }

    public function destroy(string $type, $id)
    {
        $this->assertType($type);

        BehaviorScore::type($type)->findOrFail($id)->delete();

        return redirect()->route('admin.behavior-scores.index', $type)
            ->with('status', __('Deleted successfully'));
    }
}
