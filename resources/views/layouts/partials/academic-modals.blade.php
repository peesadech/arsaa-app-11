@php
    $isAdmin = auth()->check() && collect(auth()->user()?->getRoleNames() ?? [])
        ->map(fn($r) => strtoupper($r))->intersect(['ADMIN', 'SUPERADMIN'])->isNotEmpty();
@endphp
@if($isAdmin)
    @php
        $academicYears = \App\Models\AcademicYear::where('status', 1)->orderBy('year', 'desc')->get();
        $semesters = \App\Models\Semester::where('status', 1)->orderBy('semester_number', 'asc')->get();
        $globalSetting = \App\Models\CurrentAcademicSetting::first();
    @endphp

    {{-- Session academic year/semester selector --}}
    <div id="academicYearModal" style="display:none" class="fixed inset-0 z-[9999] items-center justify-center bg-slate-900/50 backdrop-blur-sm" onclick="if(event.target===this)this.style.display='none'">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600">
                        <x-icon name="academic" class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg text-slate-900">{{ __('Semester & Academic Year') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Select semester and academic year') }}</p>
                    </div>
                </div>
                <button onclick="document.getElementById('academicYearModal').style.display='none'" class="text-slate-400 hover:text-slate-700"><x-icon name="x" class="h-5 w-5" /></button>
            </div>
            <form method="POST" action="{{ route('admin.academic-years.select-current') }}">
                @csrf
                <div class="p-5">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Semester') }}</label>
                    <div class="flex gap-2 mb-4">
                        @forelse($semesters as $sem)
                        <label class="flex-1 flex items-center justify-center gap-2 p-3 rounded-xl cursor-pointer border-2 transition {{ session('current_semester_id') == $sem->id ? 'bg-emerald-50 border-emerald-300' : 'bg-slate-50 hover:bg-slate-100 border-slate-200' }}">
                            <input type="radio" name="semester_id" value="{{ $sem->id }}" class="text-emerald-600 focus:ring-emerald-500" {{ session('current_semester_id') == $sem->id ? 'checked' : '' }}>
                            <span class="font-semibold text-sm text-slate-900">{{ __('Semester Number') }} {{ $sem->semester_number }}</span>
                        </label>
                        @empty
                        <div class="w-full text-center py-4 text-slate-400 text-sm">{{ __('No active semesters') }}</div>
                        @endforelse
                    </div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Academic Year') }}</label>
                    <div class="max-h-48 overflow-y-auto mb-2 space-y-1">
                        @forelse($academicYears as $ay)
                        <label class="w-full flex items-center justify-between p-3 rounded-xl cursor-pointer border-2 transition {{ session('current_academic_year_id') == $ay->id ? 'bg-brand-50 border-brand-300' : 'bg-slate-50 hover:bg-slate-100 border-slate-200' }}">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="academic_year_id" value="{{ $ay->id }}" class="text-brand-600 focus:ring-brand-500" {{ session('current_academic_year_id') == $ay->id ? 'checked' : '' }}>
                                <div class="w-10 h-10 rounded-xl {{ session('current_academic_year_id') == $ay->id ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-bold text-sm">{{ $ay->year }}</div>
                                <span class="font-semibold text-sm text-slate-900">{{ __('Academic Year :year', ['year' => $ay->year]) }}</span>
                            </div>
                            @if(session('current_academic_year_id') == $ay->id)<x-icon name="check" class="h-5 w-5 text-brand-600" />@endif
                        </label>
                        @empty
                        <div class="text-center py-6 text-slate-400 text-sm">{{ __('No active academic years') }}</div>
                        @endforelse
                    </div>
                </div>
                <div class="p-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('academicYearModal').style.display='none'" class="btn-secondary">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> {{ __('Select') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Global academic year/semester setting (saved to DB) --}}
    <div id="academicYearGlobalModal" style="display:none" class="fixed inset-0 z-[9999] items-center justify-center bg-slate-900/50 backdrop-blur-sm" onclick="if(event.target===this)this.style.display='none'">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600">
                        <x-icon name="academic" class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg text-slate-900">{{ __('Semester & Academic Year') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Set current system semester and academic year') }}</p>
                    </div>
                </div>
                <button onclick="document.getElementById('academicYearGlobalModal').style.display='none'" class="text-slate-400 hover:text-slate-700"><x-icon name="x" class="h-5 w-5" /></button>
            </div>
            <form method="POST" action="{{ route('admin.academic-years.select-current-global') }}">
                @csrf
                <div class="p-5">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Semester') }}</label>
                    <div class="flex gap-2 mb-4">
                        @forelse($semesters as $sem)
                        <label class="flex-1 flex items-center justify-center gap-2 p-3 rounded-xl cursor-pointer border-2 transition {{ ($globalSetting && $globalSetting->semester_id == $sem->id) ? 'bg-emerald-50 border-emerald-300' : 'bg-slate-50 hover:bg-slate-100 border-slate-200' }}">
                            <input type="radio" name="semester_id" value="{{ $sem->id }}" class="text-emerald-600 focus:ring-emerald-500" {{ ($globalSetting && $globalSetting->semester_id == $sem->id) ? 'checked' : '' }}>
                            <span class="font-semibold text-sm text-slate-900">{{ __('Semester Number') }} {{ $sem->semester_number }}</span>
                        </label>
                        @empty
                        <div class="w-full text-center py-4 text-slate-400 text-sm">{{ __('No active semesters') }}</div>
                        @endforelse
                    </div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Academic Year') }}</label>
                    <div class="max-h-48 overflow-y-auto mb-2 space-y-1">
                        @forelse($academicYears as $ay)
                        <label class="w-full flex items-center justify-between p-3 rounded-xl cursor-pointer border-2 transition {{ ($globalSetting && $globalSetting->academic_year_id == $ay->id) ? 'bg-brand-50 border-brand-300' : 'bg-slate-50 hover:bg-slate-100 border-slate-200' }}">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="academic_year_id" value="{{ $ay->id }}" class="text-brand-600 focus:ring-brand-500" {{ ($globalSetting && $globalSetting->academic_year_id == $ay->id) ? 'checked' : '' }}>
                                <div class="w-10 h-10 rounded-xl {{ ($globalSetting && $globalSetting->academic_year_id == $ay->id) ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-bold text-sm">{{ $ay->year }}</div>
                                <span class="font-semibold text-sm text-slate-900">{{ __('Academic Year :year', ['year' => $ay->year]) }}</span>
                            </div>
                            @if($globalSetting && $globalSetting->academic_year_id == $ay->id)<x-icon name="check" class="h-5 w-5 text-brand-600" />@endif
                        </label>
                        @empty
                        <div class="text-center py-6 text-slate-400 text-sm">{{ __('No active academic years') }}</div>
                        @endforelse
                    </div>
                </div>
                <div class="p-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('academicYearGlobalModal').style.display='none'" class="btn-secondary">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> {{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endif
