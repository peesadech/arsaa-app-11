<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConductCriterion;
use Illuminate\Http\Request;

class ConductCriterionController extends Controller
{
    public function index(Request $request)
    {
        $query = ConductCriterion::query();

        if ($request->filled('is_active') && in_array($request->is_active, ['1', '0'], true)) {
            $query->where('is_active', $request->is_active);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('name_cn', 'like', "%{$s}%"));
        }

        $sortBy = in_array($request->get('sort_by'), ['sort_order', 'name', 'id']) ? $request->get('sort_by') : 'sort_order';
        $sortOrder = $request->get('sort_order') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $items = $query->paginate((int) $request->get('per_page', 10))->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.conduct-criteria._rows', compact('items'))->render(),
                'meta' => [
                    'total' => $items->total(), 'per_page' => $items->perPage(),
                    'current_page' => $items->currentPage(), 'last_page' => $items->lastPage(),
                    'from' => $items->firstItem() ?? 0, 'to' => $items->lastItem() ?? 0,
                ],
            ]);
        }

        return view('admin.conduct-criteria.index', compact('items'));
    }

    public function create()
    {
        return view('admin.conduct-criteria.create');
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());
        ConductCriterion::create($this->payload($request));

        return redirect()->route('admin.conduct-criteria.index')->with('status', __('Saved successfully'));
    }

    public function edit($id)
    {
        $item = ConductCriterion::findOrFail($id);
        return view('admin.conduct-criteria.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = ConductCriterion::findOrFail($id);
        $request->validate($this->rules());
        $item->update($this->payload($request));

        return redirect()->route('admin.conduct-criteria.index')->with('status', __('Saved successfully'));
    }

    public function destroy($id)
    {
        ConductCriterion::findOrFail($id)->delete();
        return redirect()->route('admin.conduct-criteria.index')->with('status', __('Deleted successfully'));
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'name_cn' => 'nullable|string|max:150',
            'max_score' => 'required|numeric|min:1|max:1000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ];
    }

    private function payload(Request $request): array
    {
        return [
            'name' => $request->name,
            'name_cn' => $request->name_cn,
            'max_score' => $request->max_score,
            'sort_order' => (int) $request->get('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
