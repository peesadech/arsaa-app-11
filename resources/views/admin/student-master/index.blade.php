<x-layouts.admin :header="__('Student Master Data')" :subheader="__('Dropdown options used in student forms')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.students.index')">{{ __('Back') }}</x-button>
    </x-slot>

    {{-- Validation errors --}}
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-sm">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Type Tabs --}}
    @php
        $tabs = [
            \App\Models\MasterOption::TYPE_NATIONALITY => __('Nationality / Race'),
            \App\Models\MasterOption::TYPE_RELIGION => __('Religion'),
            \App\Models\MasterOption::TYPE_BLOOD_TYPE => __('Blood Type'),
            \App\Models\MasterOption::TYPE_GUARDIAN_TYPE => __('Guardian Type'),
            \App\Models\MasterOption::TYPE_DOCUMENT_TYPE => __('Document Type'),
            \App\Models\MasterOption::TYPE_PROVINCE => __('Province'),
            // 'grade_setting' => __('Grade Criteria'), // ซ่อนไว้ก่อนตามคำขอ (2026-07-06)
        ];
    @endphp
    <div class="flex items-center gap-1 overflow-x-auto mb-6 pb-1 border-b border-slate-200">
        @foreach($tabs as $key => $label)
        <a href="{{ route('admin.student-master.index', ['type' => $key]) }}"
           class="px-4 py-2.5 -mb-px text-sm font-medium whitespace-nowrap border-b-2 transition
           {{ $type === $key
               ? 'border-brand-600 text-brand-700'
               : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($type !== 'grade_setting')
    {{-- Options table + add form --}}
    <x-card>
        <form action="{{ route('admin.student-master.store') }}" method="POST" class="flex flex-wrap items-end gap-3 mb-6">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="status" value="1">
            <div class="flex-1 min-w-[140px]">
                <label class="form-label">{{ __('Name (TH)') }} *</label>
                <input type="text" name="name_th" required class="form-input">
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="form-label">{{ __('Name (EN)') }}</label>
                <input type="text" name="name_en" class="form-input">
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="form-label">{{ __('Name (CN)') }}</label>
                <input type="text" name="name_cn" class="form-input">
            </div>
            <div class="w-24">
                <label class="form-label">{{ __('Order') }}</label>
                <input type="number" name="sort_order" value="0" min="0" class="form-input">
            </div>
            <button type="submit" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('Add') }}</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:600px">
                <thead>
                    <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide border-b border-slate-100">
                        <th class="py-2 pr-3 w-16">#</th>
                        <th class="py-2 pr-3">{{ __('Name (TH)') }}</th>
                        <th class="py-2 pr-3">{{ __('Name (EN)') }}</th>
                        <th class="py-2 pr-3">{{ __('Name (CN)') }}</th>
                        <th class="py-2 pr-3 w-20">{{ __('Order') }}</th>
                        <th class="py-2 pr-3 w-24">{{ __('Status') }}</th>
                        <th class="py-2 w-24 text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($options as $i => $option)
                    <tr class="border-b border-slate-50">
                        <form action="{{ route('admin.student-master.update', $option->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <td class="py-2 pr-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                        <td class="py-2 pr-3"><input type="text" name="name_th" value="{{ $option->name_th }}" required class="form-input text-xs py-1.5"></td>
                        <td class="py-2 pr-3"><input type="text" name="name_en" value="{{ $option->name_en }}" class="form-input text-xs py-1.5"></td>
                        <td class="py-2 pr-3"><input type="text" name="name_cn" value="{{ $option->name_cn }}" class="form-input text-xs py-1.5"></td>
                        <td class="py-2 pr-3"><input type="number" name="sort_order" value="{{ $option->sort_order }}" min="0" class="form-input text-xs py-1.5 w-16"></td>
                        <td class="py-2 pr-3">
                            <select name="status" class="form-select text-xs py-1.5">
                                <option value="1" {{ $option->status == 1 ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="2" {{ $option->status == 2 ? 'selected' : '' }}>{{ __('Not Active') }}</option>
                            </select>
                        </td>
                        <td class="py-2 text-right whitespace-nowrap">
                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-emerald-600 hover:bg-emerald-50 transition shadow-sm" title="{{ __('Save') }}"><x-icon name="check" class="h-4 w-4" /></button>
                        </form>
                            <form action="{{ route('admin.student-master.destroy', $option->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to permanently remove') }} {{ $option->name_th }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-red-600 hover:bg-red-50 transition shadow-sm" title="{{ __('Delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-sm text-slate-400">{{ __('No data') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    @else
    {{-- Grade Settings --}}
    <x-card>
        <form action="{{ route('admin.student-master.grade-settings.store') }}" method="POST" class="flex flex-wrap items-end gap-3 mb-6">
            @csrf
            <div class="w-24">
                <label class="form-label">{{ __('Grade') }} *</label>
                <input type="text" name="grade" required maxlength="10" class="form-input">
            </div>
            <div class="w-28">
                <label class="form-label">{{ __('Min score') }} *</label>
                <input type="number" name="min_score" required step="0.01" min="0" max="100" class="form-input">
            </div>
            <div class="w-28">
                <label class="form-label">{{ __('Max score') }} *</label>
                <input type="number" name="max_score" required step="0.01" min="0" max="100" class="form-input">
            </div>
            <div class="w-28">
                <label class="form-label">{{ __('Result') }}</label>
                <select name="is_pass" class="form-select">
                    <option value="1">{{ __('Pass') }}</option>
                    <option value="0">{{ __('Fail') }}</option>
                </select>
            </div>
            <div class="w-24">
                <label class="form-label">{{ __('Order') }}</label>
                <input type="number" name="sort_order" value="0" min="0" class="form-input">
            </div>
            <button type="submit" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('Add') }}</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:600px">
                <thead>
                    <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide border-b border-slate-100">
                        <th class="py-2 pr-3 w-16">#</th>
                        <th class="py-2 pr-3">{{ __('Grade') }}</th>
                        <th class="py-2 pr-3">{{ __('Min score') }}</th>
                        <th class="py-2 pr-3">{{ __('Max score') }}</th>
                        <th class="py-2 pr-3 w-24">{{ __('Result') }}</th>
                        <th class="py-2 pr-3 w-20">{{ __('Order') }}</th>
                        <th class="py-2 w-24 text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($gradeSettings as $i => $setting)
                    <tr class="border-b border-slate-50">
                        <form action="{{ route('admin.student-master.grade-settings.update', $setting->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <td class="py-2 pr-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                        <td class="py-2 pr-3"><input type="text" name="grade" value="{{ $setting->grade }}" required maxlength="10" class="form-input text-xs py-1.5"></td>
                        <td class="py-2 pr-3"><input type="number" name="min_score" value="{{ $setting->min_score }}" required step="0.01" min="0" max="100" class="form-input text-xs py-1.5"></td>
                        <td class="py-2 pr-3"><input type="number" name="max_score" value="{{ $setting->max_score }}" required step="0.01" min="0" max="100" class="form-input text-xs py-1.5"></td>
                        <td class="py-2 pr-3">
                            <select name="is_pass" class="form-select text-xs py-1.5">
                                <option value="1" {{ $setting->is_pass ? 'selected' : '' }}>{{ __('Pass') }}</option>
                                <option value="0" {{ !$setting->is_pass ? 'selected' : '' }}>{{ __('Fail') }}</option>
                            </select>
                        </td>
                        <td class="py-2 pr-3"><input type="number" name="sort_order" value="{{ $setting->sort_order }}" min="0" class="form-input text-xs py-1.5 w-16"></td>
                        <td class="py-2 text-right whitespace-nowrap">
                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-emerald-600 hover:bg-emerald-50 transition shadow-sm" title="{{ __('Save') }}"><x-icon name="check" class="h-4 w-4" /></button>
                        </form>
                            <form action="{{ route('admin.student-master.grade-settings.destroy', $setting->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to permanently remove') }} {{ $setting->grade }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-red-600 hover:bg-red-50 transition shadow-sm" title="{{ __('Delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-sm text-slate-400">{{ __('No data') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
    @endif
</x-layouts.admin>
