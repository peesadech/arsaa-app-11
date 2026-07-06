@php
    $actionMeta = [
        'submit'  => ['label' => __('Submit'),  'class' => 'btn-primary'],
        'review'  => ['label' => __('Review'),  'class' => 'btn-primary'],
        'approve' => ['label' => __('Approve'), 'class' => 'btn-primary'],
        'publish' => ['label' => __('Publish'), 'class' => 'btn-primary'],
    ];
    $status = $sub?->status ?? \App\Models\CourseResultSubmission::STATUS_DRAFT;
    $color  = $sub ? $sub->badgeColor() : 'gray';
    $label  = $sub ? $sub->statusLabel() : \App\Models\CourseResultSubmission::LABELS['draft'];
@endphp
<x-layouts.admin :header="__('Result details') . ' — ' . ($oc->course->name ?? '?')"
    :subheader="($oc->grade->name_th ?? '') . ' / ' . ($oc->classroom->name ?? '') . ' · ' . __('Academic Year') . ' ' . ($oc->academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($oc->semester->semester_number ?? '?')">

    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('result-workflow.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    @if(session('status'))
    <div class="mb-6 flex items-center gap-2 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
        <x-icon name="check" class="h-4 w-4" />{{ session('status') }}
    </div>
    @endif

    <div x-data="{ rejectOpen: false }">
        {{-- Status + actions --}}
        <x-card class="mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-500">{{ __('Status') }}:</span>
                    <x-badge :color="$color">{{ $label }}</x-badge>
                    @if($status === 'rejected' && $sub?->reject_reason)
                        <span class="text-xs text-red-500">({{ __('Reason') }}: {{ $sub->reject_reason }})</span>
                    @endif
                    <span class="text-xs text-slate-400">·</span>
                    <span class="text-xs text-slate-500">{{ __('Subject Group') }}: {{ $oc->course->subjectGroup->name_th ?? '-' }}
                        @if($oc->course?->subjectGroup?->headTeacher) ({{ __('Head') }}: {{ $oc->course->subjectGroup->headTeacher->name }})@endif
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    @forelse($actions as $action)
                        @if($action === 'reject')
                            <button type="button" class="btn-danger" @click="rejectOpen = true">{{ __('Reject') }}</button>
                        @else
                            <form action="{{ route('result-workflow.transition', [$oc->id, $action]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="{{ $actionMeta[$action]['class'] }}">
                                    <x-icon name="check" class="h-4 w-4" /> {{ $actionMeta[$action]['label'] }}
                                </button>
                            </form>
                        @endif
                    @empty
                        <span class="text-xs text-slate-400">{{ __('No action available') }}</span>
                    @endforelse
                </div>
            </div>

            {{-- Workflow trail --}}
            @if($sub)
            <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <div>
                    <div class="text-slate-400">{{ __('Submitted') }}</div>
                    <div class="text-slate-700">{{ optional($sub->submitted_at)->format('d/m/Y H:i') ?? '-' }}</div>
                    <div class="text-slate-400">{{ $sub->submittedBy->name ?? '' }}</div>
                </div>
                <div>
                    <div class="text-slate-400">{{ __('Reviewed') }}</div>
                    <div class="text-slate-700">{{ optional($sub->reviewed_at)->format('d/m/Y H:i') ?? '-' }}</div>
                    <div class="text-slate-400">{{ $sub->reviewedBy->name ?? '' }}</div>
                </div>
                <div>
                    <div class="text-slate-400">{{ __('Approved') }}</div>
                    <div class="text-slate-700">{{ optional($sub->approved_at)->format('d/m/Y H:i') ?? '-' }}</div>
                    <div class="text-slate-400">{{ $sub->approvedBy->name ?? '' }}</div>
                </div>
                <div>
                    <div class="text-slate-400">{{ __('Published') }}</div>
                    <div class="text-slate-700">{{ optional($sub->published_at)->format('d/m/Y H:i') ?? '-' }}</div>
                    <div class="text-slate-400">{{ $sub->publishedBy->name ?? '' }}</div>
                </div>
            </div>
            @endif
        </x-card>

        {{-- Results table --}}
        <x-card padded="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width:560px">
                    <thead>
                        <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-100">
                            <th class="px-5 py-3 w-10">#</th>
                            <th class="px-5 py-3">{{ __('Student') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Total') }}</th>
                            <th class="px-5 py-3 text-center">{{ __('Grade') }}</th>
                            <th class="px-5 py-3 text-center">{{ __('Result') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($enrollments as $i => $enrollment)
                        @php $student = $enrollment->student; $s = $scores->get($student->id); @endphp
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-5 py-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-800">{{ $student->name_th }}</div>
                                <div class="text-xs text-slate-400">{{ $student->student_code }}</div>
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ $s && $s->total_score !== null ? $s->total_score + 0 : '-' }}</td>
                            <td class="px-5 py-3 text-center font-semibold {{ $s?->special_result ? 'text-amber-600' : 'text-brand-600' }}">
                                {{ $s ? $s->displayGrade() : '-' }}@if($s?->is_override)<span class="text-amber-500 text-xs">*</span>@endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($s?->result_status === 'pass')<x-badge color="green">{{ __('Pass') }}</x-badge>
                                @elseif($s?->result_status === 'fail')<x-badge color="red">{{ __('Fail') }}</x-badge>
                                @else <span class="text-slate-300">-</span>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-400 py-16">{{ __('No students enrolled in this classroom yet') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Reject modal --}}
        <div x-show="rejectOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
            <div class="absolute inset-0 bg-slate-900/50" @click="rejectOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Reject results') }}</h3>
                <form action="{{ route('result-workflow.transition', [$oc->id, 'reject']) }}" method="POST">
                    @csrf
                    <label class="text-xs text-slate-400">{{ __('Reason') }}</label>
                    <input type="text" name="reject_reason" maxlength="255" class="form-input text-sm mb-4" placeholder="{{ __('Reason for rejection') }}">
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn-secondary" @click="rejectOpen = false">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn-danger">{{ __('Reject') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
