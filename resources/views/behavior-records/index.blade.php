<x-layouts.admin :header="__('Behavior Scores')"
    :subheader="__('Academic Year') . ' ' . ($academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($semester->semester_number ?? '?') . ' — ' . __('Record merit / demerit per student')">

    @if(session('status'))
    <div class="mb-6 flex items-center gap-2 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
        <x-icon name="check" class="h-4 w-4" />{{ session('status') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ $errors->first() }}</div>
    @endif

    @if($openedClassrooms->isEmpty())
    <x-card class="text-center text-slate-400 py-10">
        <x-icon name="door" class="h-10 w-10 mx-auto mb-3" />
        <p class="text-sm font-medium">{{ __('No classrooms available for you this term') }}</p>
    </x-card>
    @else

    {{-- Classroom cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        @foreach($openedClassrooms as $oc)
        @php $isSelected = $selectedGradeId == $oc->grade_id && $selectedClassroomId == $oc->classroom_id; @endphp
        <a href="{{ route('behavior-records.index', ['grade_id' => $oc->grade_id, 'classroom_id' => $oc->classroom_id]) }}"
           class="block p-4 rounded-2xl border transition {{ $isSelected ? 'bg-brand-50 border-brand-200' : 'bg-white border-slate-100 shadow-card hover:border-brand-200' }}">
            <div class="text-sm font-semibold {{ $isSelected ? 'text-brand-700' : 'text-slate-800' }}">
                {{ $oc->grade->name_th ?? '' }} / {{ $oc->classroom->name ?? '' }}
            </div>
        </a>
        @endforeach
    </div>

    @if(!$selectedGradeId || !$selectedClassroomId)
    <x-card class="text-center text-slate-400 py-10">
        <x-icon name="filter" class="h-9 w-9 mx-auto mb-3" />
        <p class="text-sm font-medium">{{ __('Select a classroom above to record behavior scores') }}</p>
    </x-card>
    @elseif($enrollments->isEmpty())
    <x-card class="text-center text-slate-400 py-10">
        <x-icon name="user" class="h-8 w-8 mx-auto mb-3" />
        <p class="text-sm font-medium">{{ __('No students enrolled in this classroom yet') }}</p>
    </x-card>
    @else

    <x-card padded="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:720px">
                <thead>
                    <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 w-10">#</th>
                        <th class="px-5 py-3">{{ __('Student') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Merit') }} (+)</th>
                        <th class="px-5 py-3 text-right">{{ __('Demerit') }} (−)</th>
                        <th class="px-5 py-3 text-right">{{ __('Net') }}</th>
                        <th class="px-5 py-3 text-right w-24"></th>
                    </tr>
                </thead>
                @foreach($enrollments as $i => $enrollment)
                    @php
                        $student = $enrollment->student;
                        $rs = $records[$student->id] ?? collect();
                        $merit = $rs->where('type', 'merit')->sum(fn($r) => (float) $r->score);
                        $demerit = $rs->where('type', 'demerit')->sum(fn($r) => (float) $r->score);
                        $net = round($merit + $demerit, 2);
                    @endphp
                    <tbody x-data="{ open: false }" class="border-b border-slate-100">
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-800">{{ $student->name_th }}</div>
                                <div class="text-xs text-slate-400">{{ $student->student_code }}</div>
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-emerald-600">{{ $merit > 0 ? '+'.($merit + 0) : '0' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-red-600">{{ $demerit < 0 ? ($demerit + 0) : '0' }}</td>
                            <td class="px-5 py-3 text-right font-bold {{ $net >= 0 ? 'text-slate-800' : 'text-red-600' }}">{{ $net + 0 }}</td>
                            <td class="px-5 py-3 text-right">
                                <button type="button" @click="open = !open" class="btn-secondary text-xs py-1.5">
                                    <x-icon name="edit" class="h-4 w-4" /> {{ __('Manage') }}
                                    <span class="ml-1 text-slate-400">({{ $rs->count() }})</span>
                                </button>
                            </td>
                        </tr>
                        <tr x-show="open" x-cloak>
                            <td colspan="6" class="px-5 pb-4 bg-slate-50/60">
                                {{-- add form --}}
                                <form action="{{ route('behavior-records.store') }}" method="POST" class="flex flex-wrap items-end gap-2 py-3">
                                    @csrf
                                    <input type="hidden" name="grade_id" value="{{ $selectedGradeId }}">
                                    <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                                    <div class="w-64">
                                        <label class="text-xs text-slate-400">{{ __('Behavior item') }}</label>
                                        <select name="behavior_score_id" class="form-select text-sm" required>
                                            <option value="">{{ __('-- Select --') }}</option>
                                            <optgroup label="{{ __('Merit') }}">
                                                @foreach($meritItems as $m)
                                                <option value="{{ $m->id }}">{{ $m->name }} (+{{ $m->score + 0 }})</option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="{{ __('Demerit') }}">
                                                @foreach($demeritItems as $d)
                                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->score + 0 }})</option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="flex-1 min-w-40">
                                        <label class="text-xs text-slate-400">{{ __('Note') }}</label>
                                        <input type="text" name="note" maxlength="255" class="form-input text-sm">
                                    </div>
                                    <button type="submit" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('Add') }}</button>
                                </form>

                                {{-- history --}}
                                @if($rs->isNotEmpty())
                                <div class="border-t border-slate-100 pt-2">
                                    @foreach($rs as $r)
                                    <div class="flex items-center gap-3 py-1.5 text-sm">
                                        <x-badge :color="$r->type === 'merit' ? 'green' : 'red'">{{ $r->score + 0 }}</x-badge>
                                        <span class="text-slate-700">{{ $r->name }}</span>
                                        @if($r->note)<span class="text-xs text-slate-400">· {{ $r->note }}</span>@endif
                                        <span class="text-xs text-slate-300 ml-auto">{{ optional($r->recorded_at)->format('d/m/Y') }}</span>
                                        <form action="{{ route('behavior-records.destroy', $r->id) }}" method="POST"
                                              onsubmit="return confirm('{{ __('Delete this record?') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600 p-1" title="{{ __('Delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                        </form>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <p class="text-xs text-slate-400 py-2 border-t border-slate-100">{{ __('No records yet') }}</p>
                                @endif

                                {{-- 操行评量 / conduct assessment --}}
                                @if($conductCriteria->isNotEmpty())
                                @php $myConduct = $conductScores[$student->id] ?? collect(); @endphp
                                <form action="{{ route('behavior-records.conduct') }}" method="POST" class="border-t border-slate-100 pt-3 mt-2">
                                    @csrf
                                    <input type="hidden" name="grade_id" value="{{ $selectedGradeId }}">
                                    <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                                    <div class="text-xs font-semibold text-slate-500 mb-2">操行评量 / {{ __('Conduct') }}</div>
                                    <div class="flex flex-wrap items-end gap-2">
                                        @foreach($conductCriteria as $cc)
                                        <div class="w-28">
                                            <label class="text-xs text-slate-400">{{ $cc->name_cn ?: $cc->name }} <span class="text-slate-300">/{{ $cc->max_score + 0 }}</span></label>
                                            <input type="number" name="scores[{{ $cc->id }}]" step="0.01" min="0" max="{{ $cc->max_score + 0 }}"
                                                   value="{{ optional($myConduct->get($cc->id))->score !== null ? optional($myConduct->get($cc->id))->score + 0 : '' }}"
                                                   class="form-input text-sm text-right">
                                        </div>
                                        @endforeach
                                        <button type="submit" class="btn-secondary"><x-icon name="check" class="h-4 w-4" /> {{ __('Save') }}</button>
                                    </div>
                                </form>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                @endforeach
            </table>
        </div>
    </x-card>
    @endif
    @endif
</x-layouts.admin>
