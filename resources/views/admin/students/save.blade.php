@extends('layouts.app')

@section('content')
@php
    $isEdit = isset($student);
    $inputClass = 'w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all';
    $labelClass = 'block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1';
    $currentAddress = $isEdit ? $student->addressOfType('current') : null;
    $registeredAddress = $isEdit ? $student->addressOfType('registered') : null;
    $guardianStatusLabels = [
        'alive' => __('Alive'), 'deceased' => __('Deceased'), 'together' => __('Living together'),
        'divorced' => __('Divorced'), 'other' => __('Other'),
    ];
    $guardianFields = [
        'guardian_type_id', 'name', 'name_cn', 'age', 'race_id', 'nationality_id', 'religion_id',
        'living_status', 'address', 'phone', 'occupation', 'workplace_address', 'relationship', 'is_primary',
    ];
    $educationFields = ['school_name', 'school_location', 'last_level', 'gpa', 'graduated_at', 'note'];
    $existingGuardians = old('guardians', $isEdit ? $student->guardians->map(fn ($g) => $g->only($guardianFields))->values() : []);
    $existingEducations = old('educations', $isEdit ? $student->educationHistories->map(fn ($e) => $e->only($educationFields))->values() : []);
@endphp
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-5xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.students.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    {{ $isEdit ? __('Edit Student') : __('New Student') }}
                </h1>
                @if($isEdit)
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">{{ $student->student_code }} — {{ $student->name_th }}</p>
                @endif
            </div>
            @if($isEdit)
            <div class="ml-auto flex gap-2">
                <a href="{{ route('admin.student-reports.profile', $student->id) }}" target="_blank" class="btn-app"><i class="fas fa-id-card text-[10px]"></i> {{ __('Student Profile') }}</a>
                <a href="{{ route('admin.student-reports.transcript', $student->id) }}" target="_blank" class="btn-app"><i class="fas fa-file-alt text-[10px]"></i> {{ __('Transcript') }}</a>
            </div>
            @endif
        </div>

        {{-- Flash / Errors --}}
        @if(session('status'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm">{{ session('status') }}</div>
        @endif
        @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 text-sm">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ $isEdit ? route('admin.students.update', $student->id) : route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($isEdit) @method('PUT') @endif
            <input type="hidden" name="image_base64" id="imageBase64Input">

            {{-- Tabs --}}
            @php
                $formTabs = [
                    'general' => ['icon' => 'fa-user', 'label' => __('General Info')],
                    'address' => ['icon' => 'fa-map-marker-alt', 'label' => __('Address')],
                    'guardians' => ['icon' => 'fa-user-friends', 'label' => __('Guardians')],
                    'education' => ['icon' => 'fa-graduation-cap', 'label' => __('Education History')],
                    'documents' => ['icon' => 'fa-folder-open', 'label' => __('Documents')],
                ];
                if ($isEdit) $formTabs['history'] = ['icon' => 'fa-history', 'label' => __('Enrollment & Scores')];
            @endphp
            <div class="flex items-center gap-1 overflow-x-auto mb-6 pb-1">
                @foreach($formTabs as $key => $tab)
                <button type="button" data-tab-btn="{{ $key }}" onclick="showTab('{{ $key }}')"
                        class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all duration-200 border">
                    <i class="fas {{ $tab['icon'] }} text-[10px] mr-1"></i>{{ $tab['label'] }}
                </button>
                @endforeach
            </div>

            {{-- ==================== TAB: General ==================== --}}
            <div data-tab-panel="general" class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 space-y-5">
                <div class="flex flex-col sm:flex-row gap-6">
                    {{-- Photo --}}
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-32 h-40 rounded-2xl overflow-hidden bg-gray-100 dark:bg-[#3a3b3c] border-2 border-gray-100 dark:border-[#3a3b3c] shadow-sm">
                            <img id="photoPreview" src="{{ $isEdit && $student->image_path ? asset($student->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode($isEdit ? $student->name_th : '?') . '&color=7F9CF5&background=EBF4FF&size=256' }}" class="w-full h-full object-cover" alt="">
                        </div>
                        <label class="btn-app cursor-pointer">
                            <i class="fas fa-camera text-[10px]"></i> {{ __('Upload Photo') }}
                            <input type="file" accept="image/*" class="hidden" onchange="previewPhoto(this)">
                        </label>
                    </div>

                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Student Code') }} <span class="text-gray-300">({{ __('leave blank to auto-generate') }})</span></label>
                            <input type="text" name="student_code" value="{{ old('student_code', $student->student_code ?? '') }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Status') }} *</label>
                            <select name="status" class="{{ $inputClass }}">
                                @foreach(\App\Models\Student::STATUSES as $s)
                                <option value="{{ $s }}" {{ old('status', $student->status ?? 'studying') === $s ? 'selected' : '' }}>
                                    {{ ['studying' => __('Studying'), 'suspended' => __('Suspended'), 'resigned' => __('Resigned'), 'graduated' => __('Graduated')][$s] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Name (TH)') }} *</label>
                            <input type="text" name="name_th" required value="{{ old('name_th', $student->name_th ?? '') }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Chinese Name') }}</label>
                            <input type="text" name="name_cn" value="{{ old('name_cn', $student->name_cn ?? '') }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Citizen ID') }}</label>
                            <input type="text" name="citizen_id" maxlength="20" value="{{ old('citizen_id', $student->citizen_id ?? '') }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Birth Date') }} <span id="ageDisplay" class="text-indigo-400 font-bold"></span></label>
                            <input type="date" name="birth_date" id="birthDateInput" value="{{ old('birth_date', $isEdit ? $student->birth_date?->format('Y-m-d') : '') }}" class="{{ $inputClass }}">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="{{ $labelClass }}">{{ __('Race') }}</label>
                        <select name="race_id" class="{{ $inputClass }}">
                            <option value="">-</option>
                            @foreach($nationalities as $opt)
                            <option value="{{ $opt->id }}" {{ old('race_id', $student->race_id ?? '') == $opt->id ? 'selected' : '' }}>{{ $opt->name_th }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">{{ __('Nationality') }}</label>
                        <select name="nationality_id" class="{{ $inputClass }}">
                            <option value="">-</option>
                            @foreach($nationalities as $opt)
                            <option value="{{ $opt->id }}" {{ old('nationality_id', $student->nationality_id ?? '') == $opt->id ? 'selected' : '' }}>{{ $opt->name_th }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">{{ __('Religion') }}</label>
                        <select name="religion_id" class="{{ $inputClass }}">
                            <option value="">-</option>
                            @foreach($religions as $opt)
                            <option value="{{ $opt->id }}" {{ old('religion_id', $student->religion_id ?? '') == $opt->id ? 'selected' : '' }}>{{ $opt->name_th }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">{{ __('Blood Type') }}</label>
                        <select name="blood_type_id" class="{{ $inputClass }}">
                            <option value="">-</option>
                            @foreach($bloodTypes as $opt)
                            <option value="{{ $opt->id }}" {{ old('blood_type_id', $student->blood_type_id ?? '') == $opt->id ? 'selected' : '' }}>{{ $opt->name_th }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">{{ __('Height (cm)') }}</label>
                        <input type="number" name="height" step="0.1" min="0" max="300" value="{{ old('height', $student->height ?? '') }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">{{ __('Weight (kg)') }}</label>
                        <input type="number" name="weight" step="0.1" min="0" max="500" value="{{ old('weight', $student->weight ?? '') }}" class="{{ $inputClass }}">
                    </div>
                    <div class="col-span-2">
                        <label class="{{ $labelClass }}">{{ __('Chronic Disease') }}</label>
                        <input type="text" name="chronic_disease" value="{{ old('chronic_disease', $student->chronic_disease ?? '') }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">{{ __('Home Phone') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $student->phone ?? '') }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">{{ __('Mobile') }}</label>
                        <input type="text" name="mobile" value="{{ old('mobile', $student->mobile ?? '') }}" class="{{ $inputClass }}">
                    </div>
                    <div class="col-span-2">
                        <label class="{{ $labelClass }}">{{ __('Note') }}</label>
                        <input type="text" name="note" value="{{ old('note', $student->note ?? '') }}" class="{{ $inputClass }}">
                    </div>
                </div>
            </div>

            {{-- ==================== TAB: Address ==================== --}}
            <div data-tab-panel="address" class="hidden space-y-6">
                @foreach([['current', __('Current Address'), $currentAddress], ['registered', __('Registered Address'), $registeredAddress]] as [$type, $title, $addr])
                <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6" data-address-block="{{ $type }}">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ $title }}</h2>
                        @if($type === 'registered')
                        <button type="button" onclick="copyAddress()" class="btn-app"><i class="fas fa-copy text-[10px]"></i> {{ __('Same as current address') }}</button>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">{{ __('House No.') }}</label>
                            <input type="text" name="addresses[{{ $type }}][house_no]" value="{{ old("addresses.$type.house_no", $addr->house_no ?? '') }}" class="{{ $inputClass }}" data-addr-field="house_no">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Moo') }}</label>
                            <input type="text" name="addresses[{{ $type }}][moo]" value="{{ old("addresses.$type.moo", $addr->moo ?? '') }}" class="{{ $inputClass }}" data-addr-field="moo">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Subdistrict') }}</label>
                            <input type="text" name="addresses[{{ $type }}][subdistrict]" value="{{ old("addresses.$type.subdistrict", $addr->subdistrict ?? '') }}" class="{{ $inputClass }}" data-addr-field="subdistrict">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('District') }}</label>
                            <input type="text" name="addresses[{{ $type }}][district]" value="{{ old("addresses.$type.district", $addr->district ?? '') }}" class="{{ $inputClass }}" data-addr-field="district">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Province') }}</label>
                            <select name="addresses[{{ $type }}][province_id]" class="{{ $inputClass }}" data-addr-field="province_id">
                                <option value="">-</option>
                                @foreach($provinces as $p)
                                <option value="{{ $p->id }}" {{ old("addresses.$type.province_id", $addr->province_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name_th }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Postal Code') }}</label>
                            <input type="text" name="addresses[{{ $type }}][postal_code]" maxlength="10" value="{{ old("addresses.$type.postal_code", $addr->postal_code ?? '') }}" class="{{ $inputClass }}" data-addr-field="postal_code">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ==================== TAB: Guardians ==================== --}}
            <div data-tab-panel="guardians" class="hidden">
                <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Guardians') }} <span class="normal-case font-normal">({{ __('select one as primary guardian') }})</span></h2>
                        <button type="button" onclick="addGuardian()" class="btn-app"><i class="fas fa-plus text-[10px]"></i> {{ __('Add Guardian') }}</button>
                    </div>
                    <div id="guardiansContainer" class="space-y-4"></div>
                </div>
            </div>

            {{-- ==================== TAB: Education ==================== --}}
            <div data-tab-panel="education" class="hidden">
                <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Previous Education (Chinese)') }}</h2>
                        <button type="button" onclick="addEducation()" class="btn-app"><i class="fas fa-plus text-[10px]"></i> {{ __('Add') }}</button>
                    </div>
                    <div id="educationsContainer" class="space-y-4"></div>
                </div>
            </div>

            {{-- ==================== TAB: Documents ==================== --}}
            <div data-tab-panel="documents" class="hidden">
                <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
                    <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">{{ __('Application Documents Checklist') }}</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full" style="min-width:650px">
                            <thead>
                                <tr class="text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-[#3a3b3c]">
                                    <th class="py-2 pr-3 w-20 text-center">{{ __('Received') }}</th>
                                    <th class="py-2 pr-3">{{ __('Document') }}</th>
                                    <th class="py-2 pr-3 w-56">{{ __('Attach file') }}</th>
                                    <th class="py-2">{{ __('Note') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($documentTypes as $docType)
                                @php $doc = $isEdit ? $student->documents->firstWhere('document_type_id', $docType->id) : null; @endphp
                                <tr class="border-b border-gray-50 dark:border-[#3a3b3c]/50">
                                    <td class="py-3 pr-3 text-center">
                                        <input type="hidden" name="documents[{{ $docType->id }}][is_received]" value="0">
                                        <input type="checkbox" name="documents[{{ $docType->id }}][is_received]" value="1"
                                               {{ old("documents.{$docType->id}.is_received", $doc?->is_received) ? 'checked' : '' }}
                                               class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="py-3 pr-3 text-sm text-gray-700 dark:text-gray-300">{{ $docType->name_th }}</td>
                                    <td class="py-3 pr-3">
                                        <input type="file" name="document_files[{{ $docType->id }}]" accept=".jpg,.jpeg,.png,.pdf"
                                               class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer">
                                        @if($doc?->file_path)
                                        <a href="{{ asset($doc->file_path) }}" target="_blank" class="text-[11px] text-indigo-500 hover:underline"><i class="fas fa-paperclip mr-1"></i>{{ __('View attached file') }}</a>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <input type="text" name="documents[{{ $docType->id }}][note]" value="{{ old("documents.{$docType->id}.note", $doc->note ?? '') }}" class="{{ $inputClass }}">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ==================== TAB: History (edit only) ==================== --}}
            @if($isEdit)
            <div data-tab-panel="history" class="hidden space-y-6">
                <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Classroom History') }}</h2>
                        <a href="{{ route('admin.student-enrollments.index') }}" class="btn-app"><i class="fas fa-door-open text-[10px]"></i> {{ __('Manage Enrollment') }}</a>
                    </div>
                    @if($student->enrollments->isEmpty())
                    <p class="text-sm text-gray-400">{{ __('No enrollment history') }}</p>
                    @else
                    <div class="space-y-2">
                        @foreach($student->enrollments->sortByDesc('id') as $enrollment)
                        <div class="flex flex-wrap items-center gap-2 p-3 bg-gray-50 dark:bg-[#3a3b3c]/50 rounded-xl text-xs text-gray-600 dark:text-gray-400">
                            <span class="font-bold text-gray-800 dark:text-gray-200">{{ __('Academic Year') }} {{ $enrollment->academicYear->year ?? '?' }} / {{ __('Semester') }} {{ $enrollment->semester->semester_number ?? '?' }}</span>
                            <span>{{ $enrollment->grade->name_th ?? '' }} / {{ $enrollment->classroom->name ?? '' }}</span>
                            @php
                                $enrollColors = ['enrolled' => 'bg-emerald-50 text-emerald-600', 'moved' => 'bg-amber-50 text-amber-600', 'left' => 'bg-rose-50 text-rose-600'];
                                $enrollLabels = ['enrolled' => __('Enrolled'), 'moved' => __('Moved'), 'left' => __('Left')];
                            @endphp
                            <span class="px-2 py-0.5 rounded-lg {{ $enrollColors[$enrollment->status] ?? 'bg-gray-100 text-gray-500' }} text-[10px] font-bold uppercase">{{ $enrollLabels[$enrollment->status] ?? $enrollment->status }}</span>
                            @if($enrollment->enrolled_at)<span class="text-gray-400">{{ $enrollment->enrolled_at->format('d/m/Y') }}</span>@endif
                            @if($enrollment->note)<span class="italic text-gray-400">— {{ $enrollment->note }}</span>@endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Academic Results') }}</h2>
                        <a href="{{ route('admin.student-reports.transcript', $student->id) }}" target="_blank" class="btn-app"><i class="fas fa-file-alt text-[10px]"></i> {{ __('Transcript') }}</a>
                    </div>
                    @if($student->scores->isEmpty())
                    <p class="text-sm text-gray-400">{{ __('No scores recorded yet') }}</p>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full" style="min-width:600px">
                            <thead>
                                <tr class="text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-[#3a3b3c]">
                                    <th class="py-2 pr-3">{{ __('Academic Year') }}/{{ __('Semester') }}</th>
                                    <th class="py-2 pr-3">{{ __('Course') }}</th>
                                    <th class="py-2 pr-3 text-right">{{ __('Total') }}</th>
                                    <th class="py-2 pr-3 text-center">{{ __('Grade') }}</th>
                                    <th class="py-2 text-center">{{ __('Result') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($student->scores as $score)
                                <tr class="border-b border-gray-50 dark:border-[#3a3b3c]/50 text-xs text-gray-600 dark:text-gray-400">
                                    <td class="py-2 pr-3">{{ $score->openedCourse->academicYear->year ?? '?' }} / {{ $score->openedCourse->semester->semester_number ?? '?' }}</td>
                                    <td class="py-2 pr-3">{{ $score->openedCourse->course->name ?? '?' }}</td>
                                    <td class="py-2 pr-3 text-right font-bold">{{ $score->total_score ?? '-' }}</td>
                                    <td class="py-2 pr-3 text-center font-bold text-indigo-500">{{ $score->grade ?? '-' }}</td>
                                    <td class="py-2 text-center">
                                        @if($score->result_status === 'pass')
                                        <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase">{{ __('Pass') }}</span>
                                        @elseif($score->result_status === 'fail')
                                        <span class="px-2 py-0.5 rounded-lg bg-rose-50 text-rose-600 text-[10px] font-bold uppercase">{{ __('Fail') }}</span>
                                        @else - @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Submit --}}
            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('admin.students.index') }}" class="inline-flex items-center px-6 py-3 border-2 border-gray-100 dark:border-[#3a3b3c] text-sm font-bold rounded-2xl text-gray-600 dark:text-gray-400 bg-white dark:bg-[#242526] hover:bg-gray-50 transition-all">{{ __('Cancel') }}</a>
                <button type="submit" class="inline-flex items-center px-8 py-3 border border-transparent text-sm font-bold rounded-2xl shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-all transform hover:-translate-y-0.5 active:scale-95">
                    <i class="fas fa-save mr-2 opacity-75"></i> {{ __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ===== Tabs =====
function showTab(key) {
    document.querySelectorAll('[data-tab-panel]').forEach(p => p.classList.toggle('hidden', p.dataset.tabPanel !== key));
    document.querySelectorAll('[data-tab-btn]').forEach(b => {
        const active = b.dataset.tabBtn === key;
        b.className = 'px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all duration-200 border ' +
            (active
                ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800'
                : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#3a3b3c] border-transparent bg-white dark:bg-[#242526]');
    });
}
showTab('general');

// ===== Photo =====
function previewPhoto(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('photoPreview').src = e.target.result;
        document.getElementById('imageBase64Input').value = e.target.result;
    };
    reader.readAsDataURL(file);
}

// ===== Age =====
const birthInput = document.getElementById('birthDateInput');
function updateAge() {
    const el = document.getElementById('ageDisplay');
    if (!birthInput.value) { el.textContent = ''; return; }
    const diff = Date.now() - new Date(birthInput.value).getTime();
    const age = Math.floor(diff / (365.25 * 24 * 3600 * 1000));
    el.textContent = age >= 0 ? '(' + age + ' {{ __('years old') }})' : '';
}
birthInput.addEventListener('change', updateAge);
updateAge();

// ===== Copy address =====
function copyAddress() {
    const from = document.querySelector('[data-address-block="current"]');
    const to = document.querySelector('[data-address-block="registered"]');
    to.querySelectorAll('[data-addr-field]').forEach(field => {
        const src = from.querySelector('[data-addr-field="' + field.dataset.addrField + '"]');
        if (src) field.value = src.value;
    });
}

// ===== Guardians (dynamic) =====
const GUARDIAN_TYPES = {!! json_encode($guardianTypes->map(fn($g) => ['id' => $g->id, 'name' => $g->name_th])) !!};
const NATIONALITIES = {!! json_encode($nationalities->map(fn($n) => ['id' => $n->id, 'name' => $n->name_th])) !!};
const RELIGIONS = {!! json_encode($religions->map(fn($r) => ['id' => $r->id, 'name' => $r->name_th])) !!};
const LIVING_STATUSES = {!! json_encode(collect(\App\Models\StudentGuardian::LIVING_STATUSES)->map(fn($s) => ['id' => $s, 'name' => $guardianStatusLabels[$s] ?? $s])) !!};
const INPUT_CLASS = @json($inputClass);
const LABEL_CLASS = @json($labelClass);
let guardianIdx = 0;

function optionHtml(list, selected) {
    return '<option value="">-</option>' + list.map(o =>
        '<option value="' + o.id + '"' + (String(o.id) === String(selected ?? '') ? ' selected' : '') + '>' + o.name + '</option>').join('');
}

function addGuardian(data = {}) {
    const i = guardianIdx++;
    const div = document.createElement('div');
    div.className = 'p-4 rounded-2xl border border-gray-100 dark:border-[#3a3b3c] bg-gray-50/50 dark:bg-[#3a3b3c]/30 space-y-3';
    div.innerHTML = `
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-indigo-500">
                <input type="radio" name="primary_guardian" value="${i}" ${data.is_primary ? 'checked' : ''} class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                {{ __('Primary guardian') }}
            </label>
            <button type="button" onclick="this.closest('[data-guardian]').remove()" class="text-rose-400 hover:text-rose-600 text-xs font-bold"><i class="fas fa-trash-alt mr-1"></i>{{ __('Remove') }}</button>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div><label class="${LABEL_CLASS}">{{ __('Guardian Type') }}</label><select name="guardians[${i}][guardian_type_id]" class="${INPUT_CLASS}">${optionHtml(GUARDIAN_TYPES, data.guardian_type_id)}</select></div>
            <div><label class="${LABEL_CLASS}">{{ __('Name') }} *</label><input type="text" name="guardians[${i}][name]" value="${data.name ?? ''}" class="${INPUT_CLASS}"></div>
            <div><label class="${LABEL_CLASS}">{{ __('Chinese Name') }}</label><input type="text" name="guardians[${i}][name_cn]" value="${data.name_cn ?? ''}" class="${INPUT_CLASS}"></div>
            <div><label class="${LABEL_CLASS}">{{ __('Age') }}</label><input type="number" name="guardians[${i}][age]" min="0" max="150" value="${data.age ?? ''}" class="${INPUT_CLASS}"></div>
            <div><label class="${LABEL_CLASS}">{{ __('Race') }}</label><select name="guardians[${i}][race_id]" class="${INPUT_CLASS}">${optionHtml(NATIONALITIES, data.race_id)}</select></div>
            <div><label class="${LABEL_CLASS}">{{ __('Nationality') }}</label><select name="guardians[${i}][nationality_id]" class="${INPUT_CLASS}">${optionHtml(NATIONALITIES, data.nationality_id)}</select></div>
            <div><label class="${LABEL_CLASS}">{{ __('Religion') }}</label><select name="guardians[${i}][religion_id]" class="${INPUT_CLASS}">${optionHtml(RELIGIONS, data.religion_id)}</select></div>
            <div><label class="${LABEL_CLASS}">{{ __('Living Status') }}</label><select name="guardians[${i}][living_status]" class="${INPUT_CLASS}">${optionHtml(LIVING_STATUSES, data.living_status)}</select></div>
            <div class="col-span-2"><label class="${LABEL_CLASS}">{{ __('Address') }}</label><input type="text" name="guardians[${i}][address]" value="${data.address ?? ''}" class="${INPUT_CLASS}"></div>
            <div><label class="${LABEL_CLASS}">{{ __('Phone') }}</label><input type="text" name="guardians[${i}][phone]" value="${data.phone ?? ''}" class="${INPUT_CLASS}"></div>
            <div><label class="${LABEL_CLASS}">{{ __('Occupation') }}</label><input type="text" name="guardians[${i}][occupation]" value="${data.occupation ?? ''}" class="${INPUT_CLASS}"></div>
            <div class="col-span-2"><label class="${LABEL_CLASS}">{{ __('Workplace Address') }}</label><input type="text" name="guardians[${i}][workplace_address]" value="${data.workplace_address ?? ''}" class="${INPUT_CLASS}"></div>
            <div class="col-span-2"><label class="${LABEL_CLASS}">{{ __('Relationship to student') }}</label><input type="text" name="guardians[${i}][relationship]" value="${data.relationship ?? ''}" class="${INPUT_CLASS}"></div>
        </div>`;
    div.setAttribute('data-guardian', '');
    document.getElementById('guardiansContainer').appendChild(div);
}

// ===== Education histories (dynamic) =====
let eduIdx = 0;
function addEducation(data = {}) {
    const i = eduIdx++;
    const div = document.createElement('div');
    div.className = 'p-4 rounded-2xl border border-gray-100 dark:border-[#3a3b3c] bg-gray-50/50 dark:bg-[#3a3b3c]/30';
    div.innerHTML = `
        <div class="flex justify-end mb-2">
            <button type="button" onclick="this.closest('[data-education]').remove()" class="text-rose-400 hover:text-rose-600 text-xs font-bold"><i class="fas fa-trash-alt mr-1"></i>{{ __('Remove') }}</button>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <div><label class="${LABEL_CLASS}">{{ __('School Name') }} *</label><input type="text" name="educations[${i}][school_name]" value="${data.school_name ?? ''}" class="${INPUT_CLASS}"></div>
            <div><label class="${LABEL_CLASS}">{{ __('School Location') }}</label><input type="text" name="educations[${i}][school_location]" value="${data.school_location ?? ''}" class="${INPUT_CLASS}"></div>
            <div><label class="${LABEL_CLASS}">{{ __('Last Level Completed') }}</label><input type="text" name="educations[${i}][last_level]" value="${data.last_level ?? ''}" class="${INPUT_CLASS}"></div>
            <div><label class="${LABEL_CLASS}">{{ __('GPA') }}</label><input type="number" name="educations[${i}][gpa]" step="0.01" min="0" max="4" value="${data.gpa ?? ''}" class="${INPUT_CLASS}"></div>
            <div><label class="${LABEL_CLASS}">{{ __('Graduated (month/year)') }}</label><input type="text" name="educations[${i}][graduated_at]" value="${data.graduated_at ?? ''}" placeholder="03/2568" class="${INPUT_CLASS}"></div>
            <div><label class="${LABEL_CLASS}">{{ __('Note') }}</label><input type="text" name="educations[${i}][note]" value="${data.note ?? ''}" class="${INPUT_CLASS}"></div>
        </div>`;
    div.setAttribute('data-education', '');
    document.getElementById('educationsContainer').appendChild(div);
}

// ===== Initial data (edit mode / validation redisplay) =====
const EXISTING_GUARDIANS = @json($existingGuardians);
const EXISTING_EDUCATIONS = @json($existingEducations);

(Array.isArray(EXISTING_GUARDIANS) ? EXISTING_GUARDIANS : Object.values(EXISTING_GUARDIANS)).forEach(g => addGuardian(g));
(Array.isArray(EXISTING_EDUCATIONS) ? EXISTING_EDUCATIONS : Object.values(EXISTING_EDUCATIONS)).forEach(e => addEducation(e));
if (!document.querySelector('[data-guardian]')) addGuardian({is_primary: true});
</script>
@endsection
