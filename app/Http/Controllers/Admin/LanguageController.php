<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;

class LanguageController extends Controller
{
    public function index()
    {
        return view('admin.languages.index');
    }

    public function data(Request $request)
    {
        $languages = Language::select('languages.*');

        if ($request->filled('status')) {
            $languages->where('status', $request->status);
        }

        return DataTables::of($languages)
            ->addColumn('flag_display', function ($lang) {
                return '<span class="text-2xl">' . ($lang->flag ?? '') . '</span>';
            })
            ->addColumn('default_badge', function ($lang) {
                if ($lang->is_default) {
                    return '<span class="px-2 py-1 rounded-lg bg-amber-50 text-amber-600 text-[10px] font-bold uppercase tracking-wider">' . __('Yes') . '</span>';
                }
                return '<span class="text-gray-400 text-[10px]">-</span>';
            })
            ->addColumn('status_badge', function ($lang) {
                $statusText = $lang->status == 1 ? __('Active') : __('Not Active');
                $colorClass = $lang->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($lang) {
                $editUrl = route('admin.languages.edit', $lang->id);
                $transUrl = route('admin.languages.translations', $lang->code);
                $btn = '<div class="flex justify-end space-x-2">';
                $btn .= '<a href="' . $transUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-indigo-500 hover:bg-indigo-50 transition-all duration-200 shadow-sm" title="' . __('Translations') . '"><i class="fas fa-language text-xs"></i></a>';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="' . __('Edit') . '"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $lang->id . ', \'' . addslashes($lang->name) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="' . __('Delete') . '"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['flag_display', 'default_badge', 'status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.languages.save');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|unique:languages',
            'name' => 'required|string|max:255',
            'native_name' => 'required|string|max:255',
            'flag' => 'nullable|string|max:10',
            'direction' => 'required|in:ltr,rtl',
            'is_default' => 'nullable|boolean',
            'status' => 'required|in:1,2',
            'sort_order' => 'nullable|integer',
        ]);

        $data['is_default'] = $request->boolean('is_default');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['is_default']) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        Language::create($data);

        // Create JSON file if it doesn't exist
        $langFile = lang_path($data['code'] . '.json');
        if (!File::exists($langFile)) {
            $enFile = lang_path('en.json');
            if (File::exists($enFile)) {
                $enTranslations = json_decode(File::get($enFile), true);
                $newTranslations = array_combine(array_keys($enTranslations), array_fill(0, count($enTranslations), ''));
                File::put($langFile, json_encode($newTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                File::put($langFile, json_encode(new \stdClass(), JSON_PRETTY_PRINT));
            }
        }

        return redirect()->route('admin.languages.index')->with('status', __('Language') . ' ' . __('created successfully!'));
    }

    public function edit($id)
    {
        $language = Language::findOrFail($id);
        return view('admin.languages.save', compact('language'));
    }

    public function update(Request $request, $id)
    {
        $language = Language::findOrFail($id);

        $data = $request->validate([
            'code' => 'required|string|max:10|unique:languages,code,' . $id,
            'name' => 'required|string|max:255',
            'native_name' => 'required|string|max:255',
            'flag' => 'nullable|string|max:10',
            'direction' => 'required|in:ltr,rtl',
            'is_default' => 'nullable|boolean',
            'status' => 'required|in:1,2',
            'sort_order' => 'nullable|integer',
        ]);

        $data['is_default'] = $request->boolean('is_default');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['is_default']) {
            Language::where('is_default', true)->where('id', '!=', $id)->update(['is_default' => false]);
        }

        // Rename JSON file if code changed
        $oldCode = $language->code;
        if ($oldCode !== $data['code']) {
            $oldFile = lang_path($oldCode . '.json');
            $newFile = lang_path($data['code'] . '.json');
            if (File::exists($oldFile) && !File::exists($newFile)) {
                File::move($oldFile, $newFile);
            }
        }

        $language->update($data);

        return redirect()->route('admin.languages.index')->with('status', __('Language') . ' ' . __('updated successfully!'));
    }

    public function destroy($id)
    {
        $language = Language::findOrFail($id);

        if ($language->is_default) {
            return redirect()->route('admin.languages.index')->with('status', 'Cannot delete the default language.');
        }

        $language->delete();

        return redirect()->route('admin.languages.index')->with('status', __('Language') . ' ' . __('deleted successfully!'));
    }

    public function translations($code)
    {
        $language = Language::where('code', $code)->firstOrFail();
        $langFile = lang_path($code . '.json');
        $enFile = lang_path('en.json');

        $translations = [];
        $enTranslations = [];

        if (File::exists($enFile)) {
            $enTranslations = json_decode(File::get($enFile), true) ?? [];
        }

        if (File::exists($langFile)) {
            $translations = json_decode(File::get($langFile), true) ?? [];
        }

        // Merge: ensure all EN keys exist in target
        foreach ($enTranslations as $key => $value) {
            if (!array_key_exists($key, $translations)) {
                $translations[$key] = '';
            }
        }

        ksort($translations);

        return view('admin.languages.translations', compact('language', 'translations', 'enTranslations'));
    }

    public function updateTranslations(Request $request, $code)
    {
        $language = Language::where('code', $code)->firstOrFail();

        $translations = $request->input('translations', []);
        $langFile = lang_path($code . '.json');

        ksort($translations);
        File::put($langFile, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return redirect()->route('admin.languages.translations', $code)->with('status', __('Translations') . ' ' . __('updated successfully!'));
    }

    public function switchLocale($code)
    {
        $language = Language::where('code', $code)->where('status', 1)->first();
        if ($language) {
            session(['locale' => $code]);
        }

        return redirect()->back();
    }
}
