@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-5xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.students.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Student Master Data') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">{{ __('Dropdown options used in student forms') }}</p>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('status'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm">
            {{ session('status') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 text-sm">
            {{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 text-sm">
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
                'grade_setting' => __('Grade Criteria'),
            ];
        @endphp
        <div class="flex items-center gap-1 overflow-x-auto mb-6 pb-1">
            @foreach($tabs as $key => $label)
            <a href="{{ route('admin.student-master.index', ['type' => $key]) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all duration-200 border
               {{ $type === $key
                   ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800'
                   : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#3a3b3c] border-transparent bg-white dark:bg-[#242526]' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        @if($type !== 'grade_setting')
        {{-- Options table + add form --}}
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
            <form action="{{ route('admin.student-master.store') }}" method="POST" class="flex flex-wrap items-end gap-2 mb-6">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="status" value="1">
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Name (TH)') }} *</label>
                    <input type="text" name="name_th" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                </div>
                <div class="flex-1 min-w-[120px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Name (EN)') }}</label>
                    <input type="text" name="name_en" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                </div>
                <div class="flex-1 min-w-[120px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Name (CN)') }}</label>
                    <input type="text" name="name_cn" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                </div>
                <div class="w-24">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Order') }}</label>
                    <input type="number" name="sort_order" value="0" min="0" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                </div>
                <button type="submit" class="btn-app"><i class="fas fa-plus text-[10px]"></i> {{ __('Add') }}</button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full" style="min-width:600px">
                    <thead>
                        <tr class="text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-[#3a3b3c]">
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
                        <tr class="border-b border-gray-50 dark:border-[#3a3b3c]/50">
                            <form action="{{ route('admin.student-master.update', $option->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <td class="py-2 pr-3 text-xs text-gray-400">{{ $i + 1 }}</td>
                            <td class="py-2 pr-3"><input type="text" name="name_th" value="{{ $option->name_th }}" required class="w-full px-3 py-1.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-lg text-xs text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none"></td>
                            <td class="py-2 pr-3"><input type="text" name="name_en" value="{{ $option->name_en }}" class="w-full px-3 py-1.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-lg text-xs text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none"></td>
                            <td class="py-2 pr-3"><input type="text" name="name_cn" value="{{ $option->name_cn }}" class="w-full px-3 py-1.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-lg text-xs text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none"></td>
                            <td class="py-2 pr-3"><input type="number" name="sort_order" value="{{ $option->sort_order }}" min="0" class="w-16 px-2 py-1.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-lg text-xs text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none"></td>
                            <td class="py-2 pr-3">
                                <select name="status" class="px-2 py-1.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-lg text-xs text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none">
                                    <option value="1" {{ $option->status == 1 ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="2" {{ $option->status == 2 ? 'selected' : '' }}>{{ __('Not Active') }}</option>
                                </select>
                            </td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-[#242526] border border-gray-100 dark:border-[#3a3b3c] text-emerald-500 hover:bg-emerald-50 transition-all shadow-sm" title="{{ __('Save') }}"><i class="fas fa-save text-xs"></i></button>
                            </form>
                                <form action="{{ route('admin.student-master.destroy', $option->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to permanently remove') }} {{ $option->name_th }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-[#242526] border border-gray-100 dark:border-[#3a3b3c] text-rose-500 hover:bg-rose-50 transition-all shadow-sm" title="{{ __('Delete') }}"><i class="fas fa-trash-alt text-xs"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-sm text-gray-400">{{ __('No data') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @else
        {{-- Grade Settings --}}
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
            <form action="{{ route('admin.student-master.grade-settings.store') }}" method="POST" class="flex flex-wrap items-end gap-2 mb-6">
                @csrf
                <div class="w-24">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Grade') }} *</label>
                    <input type="text" name="grade" required maxlength="10" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="w-28">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Min score') }} *</label>
                    <input type="number" name="min_score" required step="0.01" min="0" max="100" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="w-28">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Max score') }} *</label>
                    <input type="number" name="max_score" required step="0.01" min="0" max="100" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="w-28">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Result') }}</label>
                    <select name="is_pass" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none">
                        <option value="1">{{ __('Pass') }}</option>
                        <option value="0">{{ __('Fail') }}</option>
                    </select>
                </div>
                <div class="w-24">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Order') }}</label>
                    <input type="number" name="sort_order" value="0" min="0" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <button type="submit" class="btn-app"><i class="fas fa-plus text-[10px]"></i> {{ __('Add') }}</button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full" style="min-width:600px">
                    <thead>
                        <tr class="text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-[#3a3b3c]">
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
                        <tr class="border-b border-gray-50 dark:border-[#3a3b3c]/50">
                            <form action="{{ route('admin.student-master.grade-settings.update', $setting->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <td class="py-2 pr-3 text-xs text-gray-400">{{ $i + 1 }}</td>
                            <td class="py-2 pr-3"><input type="text" name="grade" value="{{ $setting->grade }}" required maxlength="10" class="w-full px-3 py-1.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-lg text-xs text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none"></td>
                            <td class="py-2 pr-3"><input type="number" name="min_score" value="{{ $setting->min_score }}" required step="0.01" min="0" max="100" class="w-full px-3 py-1.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-lg text-xs text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none"></td>
                            <td class="py-2 pr-3"><input type="number" name="max_score" value="{{ $setting->max_score }}" required step="0.01" min="0" max="100" class="w-full px-3 py-1.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-lg text-xs text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none"></td>
                            <td class="py-2 pr-3">
                                <select name="is_pass" class="px-2 py-1.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-lg text-xs text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none">
                                    <option value="1" {{ $setting->is_pass ? 'selected' : '' }}>{{ __('Pass') }}</option>
                                    <option value="0" {{ !$setting->is_pass ? 'selected' : '' }}>{{ __('Fail') }}</option>
                                </select>
                            </td>
                            <td class="py-2 pr-3"><input type="number" name="sort_order" value="{{ $setting->sort_order }}" min="0" class="w-16 px-2 py-1.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-lg text-xs text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none"></td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-[#242526] border border-gray-100 dark:border-[#3a3b3c] text-emerald-500 hover:bg-emerald-50 transition-all shadow-sm" title="{{ __('Save') }}"><i class="fas fa-save text-xs"></i></button>
                            </form>
                                <form action="{{ route('admin.student-master.grade-settings.destroy', $setting->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to permanently remove') }} {{ $setting->grade }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-[#242526] border border-gray-100 dark:border-[#3a3b3c] text-rose-500 hover:bg-rose-50 transition-all shadow-sm" title="{{ __('Delete') }}"><i class="fas fa-trash-alt text-xs"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-sm text-gray-400">{{ __('No data') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
