@php
    $present = $statuses->firstWhere('code', 'PRESENT');
    $remarkMap = $statuses->mapWithKeys(fn ($s) => [$s->id => (bool) $s->is_require_remark]);
    $tabs = [
        'attendance'   => __('Attendance'),
        'teaching_log' => __('Teaching Log'),
        'homework'     => __('Homework'),
        'assessment'   => __('Assessment'),
        'files'        => __('Files'),
        'photos'       => __('Photos'),
    ];
    $statusColor = ['OPEN' => 'green', 'CLOSED' => 'gray', 'CANCELLED' => 'red', 'POSTPONED' => 'amber'][$session->status] ?? 'gray';
@endphp

<x-layouts.admin
    :header="$session->course?->name ?? __('Class Session')"
    :subheader="($session->grade?->name_th).' / '.($session->classroom?->name).' · '.$session->session_date->format('d M Y').($session->start_time ? ' · '.$session->start_time.'–'.$session->end_time : '')">

    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('class-sessions.today', ['date' => $session->session_date->toDateString()])">{{ __('Back') }}</x-button>
    </x-slot>

    {{-- Session status bar --}}
    <div class="mb-5">
        <x-card>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-500">{{ __('Session status') }}:</span>
                    <x-badge :color="$statusColor">{{ __($session->status) }}</x-badge>
                    @if($isAdmin && $session->teacher)
                        <span class="text-sm text-slate-500">· {{ $session->teacher->name }}</span>
                    @endif
                </div>
                <form method="POST" action="{{ route('class-sessions.status', $session->id) }}" class="flex items-center gap-2">
                    @csrf
                    <select name="status" class="form-select rounded-lg w-auto text-sm">
                        @foreach(\App\Models\ClassSession::STATUSES as $st)
                            <option value="{{ $st }}" {{ $session->status === $st ? 'selected' : '' }}>{{ __($st) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-secondary">{{ __('Update') }}</button>
                </form>
            </div>
        </x-card>
    </div>

    <div x-data="{ tab: 'attendance' }">
        {{-- Tabs --}}
        <div class="border-b border-slate-200 mb-5 overflow-x-auto">
            <nav class="flex gap-1 min-w-max">
                @foreach($tabs as $key => $label)
                    <button type="button" @click="tab = '{{ $key }}'"
                            class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition whitespace-nowrap"
                            :class="tab === '{{ $key }}' ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-800'">
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Attendance tab --}}
        <div x-show="tab === 'attendance'">
            <script>
                window.ATT_REMARK = {!! json_encode($remarkMap) !!};
                function attRequiresRemark(id){ return !!window.ATT_REMARK[id]; }
            </script>

            @if($students->isEmpty())
                <x-card>
                    <div class="text-center py-10 text-slate-400">
                        <x-icon name="users" class="h-8 w-8 mx-auto mb-2" />
                        <p class="text-sm font-medium">{{ __('No students enrolled in this classroom yet') }}</p>
                    </div>
                </x-card>
            @else
                <form method="POST" action="{{ route('class-sessions.save-attendance', $session->id) }}">
                    @csrf
                    <x-card padded="false">
                        {{-- toolbar: legend + mark all present --}}
                        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center gap-3">
                            <button type="button"
                                    @click="window.dispatchEvent(new CustomEvent('set-all-present'))"
                                    class="btn-primary">
                                <x-icon name="check" class="h-4 w-4" /> {{ __('Select all = Present') }}
                            </button>
                            <div class="flex flex-wrap items-center gap-1.5 ml-auto">
                                @foreach($statuses as $st)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-bold"
                                          style="background: {{ $st->color }}1a; color: {{ $st->color }}; border:1px solid {{ $st->color }}66;">
                                        {{ $st->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm" style="min-width:640px">
                                <thead>
                                    <tr class="text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50">
                                        <th class="px-4 py-3 w-10">#</th>
                                        <th class="px-4 py-3">{{ __('Student') }}</th>
                                        <th class="px-4 py-3">{{ __('Status') }}</th>
                                        <th class="px-4 py-3 w-64">{{ __('Remark') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $i => $student)
                                        @php $rec = $records->get($student->id); @endphp
                                        <tr class="border-t border-slate-100 align-top"
                                            x-data="{ statusId: {{ $rec?->attendance_status_id ?? 'null' }}, remark: @js($rec?->remark ?? '') }"
                                            x-on:set-all-present.window="statusId = {{ $present?->id ?? 'null' }}">
                                            <td class="px-4 py-3 text-slate-400">{{ $i + 1 }}</td>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-slate-800">{{ $student->name_th }}</div>
                                                @if($student->name_cn)
                                                    <div class="text-[13px] text-slate-500">{{ $student->name_cn }}</div>
                                                @endif
                                                <div class="text-[11px] text-slate-400">{{ $student->student_code }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="hidden" name="attendance[{{ $student->id }}][attendance_status_id]" :value="statusId ?? ''">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($statuses as $st)
                                                        <button type="button" @click="statusId = {{ $st->id }}"
                                                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition"
                                                                :class="statusId === {{ $st->id }} ? 'ring-2 ring-offset-1 ring-slate-400' : 'opacity-45 hover:opacity-100'"
                                                                style="background: {{ $st->color }}1a; color: {{ $st->color }}; border:1px solid {{ $st->color }}66;">
                                                            {{ $st->name }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text" name="attendance[{{ $student->id }}][remark]" x-model="remark"
                                                       class="form-input" placeholder="{{ __('Remark') }}"
                                                       :required="attRequiresRemark(statusId)"
                                                       :class="attRequiresRemark(statusId) ? 'border-amber-300' : ''">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="px-5 py-4 border-t border-slate-100 flex justify-end">
                            <button type="submit" class="btn-primary">
                                <x-icon name="check" class="h-4 w-4" /> {{ __('Save Attendance') }}
                            </button>
                        </div>
                    </x-card>
                </form>
            @endif
        </div>

        {{-- Teaching Log tab --}}
        <div x-show="tab === 'teaching_log'" x-cloak>
            <form method="POST" action="{{ route('class-sessions.save-teaching-log', $session->id) }}">
                @csrf
                <x-card :title="__('Teaching Log')" :description="__('Record what was taught in this session')">
                    <div class="space-y-4">
                        <x-form.input :label="__('Topic taught')" name="topic" :value="$teachingLog?->topic" />
                        <x-form.textarea :label="__('Content')" name="content" rows="4" :value="$teachingLog?->content" />
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-form.textarea :label="__('Notes')" name="notes" rows="3" :value="$teachingLog?->notes" />
                            <x-form.textarea :label="__('Problems found')" name="problems" rows="3" :value="$teachingLog?->problems" />
                        </div>
                        <x-form.textarea :label="__('Assigned work')" name="assigned_work" rows="3" :value="$teachingLog?->assigned_work" />
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> {{ __('Save') }}</button>
                    </div>
                </x-card>
            </form>
        </div>

        {{-- Homework tab --}}
        <div x-show="tab === 'homework'" x-cloak class="space-y-5">
            <x-card :title="__('Add homework')">
                <form method="POST" action="{{ route('class-sessions.store-homework', $session->id) }}" class="space-y-4">
                    @csrf
                    <x-form.input :label="__('Title')" name="title" required />
                    <x-form.textarea :label="__('Description')" name="description" rows="3" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input :label="__('Due date')" name="due_date" type="date" />
                        <x-form.input :label="__('Max score')" name="max_score" type="number" />
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('Add homework') }}</button>
                    </div>
                </form>
            </x-card>

            <x-card padded="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" style="min-width:600px">
                        <thead>
                            <tr class="text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50">
                                <th class="px-4 py-3">{{ __('Title') }}</th>
                                <th class="px-4 py-3">{{ __('Due date') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('Max score') }}</th>
                                <th class="px-4 py-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($homeworks as $hw)
                                <tr class="border-t border-slate-100">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $hw->title }}</div>
                                        @if($hw->description)<div class="text-xs text-slate-500 mt-0.5">{{ $hw->description }}</div>@endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ optional($hw->due_date)->format('d M Y') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">{{ $hw->max_score !== null ? rtrim(rtrim(number_format($hw->max_score, 2), '0'), '.') : '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('class-sessions.delete-homework', [$session->id, $hw->id]) }}"
                                              onsubmit="return confirm('{{ __('Are you sure you want to permanently remove') }} {{ addslashes($hw->title) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-ghost p-2 text-red-500 hover:bg-red-50"><x-icon name="trash" class="h-4 w-4" /></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-slate-400 py-12">{{ __('No homework yet') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        {{-- Assessment tab --}}
        @php $assessmentTypes = ['quiz' => __('Quiz'), 'assignment' => __('Assignment'), 'participation' => __('Participation'), 'score' => __('Score')]; @endphp
        <div x-show="tab === 'assessment'" x-cloak class="space-y-5">
            <x-card :title="__('Add assessment')" :description="__('Quiz, assignment, participation or score — linked to this session')">
                <form method="POST" action="{{ route('class-sessions.store-assessment', $session->id) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-form.select :label="__('Type')" name="type" :options="$assessmentTypes" selected="quiz" />
                        <div class="md:col-span-2">
                            <x-form.input :label="__('Title')" name="title" required />
                        </div>
                    </div>
                    <x-form.textarea :label="__('Description')" name="description" rows="2" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input :label="__('Max score')" name="max_score" type="number" />
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('Add assessment') }}</button>
                    </div>
                </form>
            </x-card>

            <x-card padded="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" style="min-width:600px">
                        <thead>
                            <tr class="text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50">
                                <th class="px-4 py-3">{{ __('Type') }}</th>
                                <th class="px-4 py-3">{{ __('Title') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('Max score') }}</th>
                                <th class="px-4 py-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assessments as $as)
                                <tr class="border-t border-slate-100">
                                    <td class="px-4 py-3"><x-badge color="blue">{{ $assessmentTypes[$as->type] ?? $as->type }}</x-badge></td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $as->title }}</div>
                                        @if($as->description)<div class="text-xs text-slate-500 mt-0.5">{{ $as->description }}</div>@endif
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ $as->max_score !== null ? rtrim(rtrim(number_format($as->max_score, 2), '0'), '.') : '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('class-sessions.delete-assessment', [$session->id, $as->id]) }}"
                                              onsubmit="return confirm('{{ __('Are you sure you want to permanently remove') }} {{ addslashes($as->title) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-ghost p-2 text-red-500 hover:bg-red-50"><x-icon name="trash" class="h-4 w-4" /></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-slate-400 py-12">{{ __('No assessments yet') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        {{-- Files tab --}}
        <div x-show="tab === 'files'" x-cloak class="space-y-5">
            <x-card :title="__('Upload file')" :description="__('Teaching materials attached to this session')">
                <form method="POST" action="{{ route('class-sessions.upload-file', $session->id) }}" enctype="multipart/form-data"
                      class="flex flex-wrap items-end gap-3">
                    @csrf
                    <input type="hidden" name="kind" value="file">
                    <div class="flex-1 min-w-[220px]">
                        <label class="form-label">{{ __('File') }}</label>
                        <input type="file" name="file" required class="form-input">
                        @error('file')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn-primary"><x-icon name="upload" class="h-4 w-4" /> {{ __('Upload') }}</button>
                </form>
            </x-card>

            <x-card padded="false">
                <div class="divide-y divide-slate-100">
                    @forelse($files as $f)
                        <div class="flex items-center gap-3 px-5 py-3">
                            <x-icon name="clipboard" class="h-5 w-5 text-slate-400" />
                            <div class="min-w-0 flex-1">
                                <a href="{{ $f->url }}" target="_blank" class="font-medium text-slate-800 hover:text-brand-600 truncate block">{{ $f->original_name }}</a>
                                <div class="text-[11px] text-slate-400">{{ $f->size ? number_format($f->size / 1024, 0) . ' KB' : '' }}</div>
                            </div>
                            <a href="{{ $f->url }}" target="_blank" class="btn-ghost p-2" title="{{ __('Download') }}"><x-icon name="download" class="h-4 w-4" /></a>
                            <form method="POST" action="{{ route('class-sessions.delete-file', [$session->id, $f->id]) }}" onsubmit="return confirm('{{ __('Confirm Delete') }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-ghost p-2 text-red-500 hover:bg-red-50"><x-icon name="trash" class="h-4 w-4" /></button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center text-slate-400 py-12">{{ __('No files yet') }}</div>
                    @endforelse
                </div>
            </x-card>
        </div>

        {{-- Photos tab --}}
        <div x-show="tab === 'photos'" x-cloak class="space-y-5">
            <x-card :title="__('Upload photo')" :description="__('Session activity photos')">
                <form method="POST" action="{{ route('class-sessions.upload-file', $session->id) }}" enctype="multipart/form-data"
                      class="flex flex-wrap items-end gap-3">
                    @csrf
                    <input type="hidden" name="kind" value="photo">
                    <div class="flex-1 min-w-[220px]">
                        <label class="form-label">{{ __('Photo') }}</label>
                        <input type="file" name="file" accept="image/*" required class="form-input">
                        @error('file')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn-primary"><x-icon name="image" class="h-4 w-4" /> {{ __('Upload') }}</button>
                </form>
            </x-card>

            @if($photos->isEmpty())
                <x-card><div class="text-center text-slate-400 py-12">{{ __('No photos yet') }}</div></x-card>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($photos as $p)
                        <div class="relative group">
                            <a href="{{ $p->url }}" target="_blank">
                                <img src="{{ $p->url }}" alt="{{ $p->original_name }}" class="w-full h-36 object-cover rounded-xl border border-slate-200">
                            </a>
                            <form method="POST" action="{{ route('class-sessions.delete-file', [$session->id, $p->id]) }}"
                                  onsubmit="return confirm('{{ __('Confirm Delete') }}?')"
                                  class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg bg-white/90 text-red-500 shadow hover:bg-white"><x-icon name="trash" class="h-4 w-4" /></button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
