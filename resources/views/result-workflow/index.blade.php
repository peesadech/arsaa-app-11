@php
    $actionMeta = [
        'submit'  => ['label' => __('Submit'),  'class' => 'btn-primary'],
        'review'  => ['label' => __('Review'),  'class' => 'btn-primary'],
        'approve' => ['label' => __('Approve'), 'class' => 'btn-primary'],
        'publish' => ['label' => __('Publish'), 'class' => 'btn-primary'],
    ];
    $entryRoute = $isAdmin ? 'admin.student-scores.entry' : 'teacher.scores.entry';
@endphp
<x-layouts.admin :header="__('Result Approval')"
    :subheader="__('Academic Year') . ' ' . ($academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($semester->semester_number ?? '?') . ' — ' . __('Submit and approve academic results')">

    @if(session('status'))
    <div class="mb-6 flex items-center gap-2 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
        <x-icon name="check" class="h-4 w-4" />{{ session('status') }}
    </div>
    @endif

    <div x-data="{ rejectTarget: null }">
        <x-card padded="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width:760px">
                    <thead>
                        <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-100">
                            <th class="px-5 py-3 w-10">#</th>
                            <th class="px-5 py-3">{{ __('Grade') }} / {{ __('Classroom') }}</th>
                            <th class="px-5 py-3">{{ __('Course') }}</th>
                            <th class="px-5 py-3">{{ __('Subject Group') }}</th>
                            <th class="px-5 py-3 text-center">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $i => $row)
                        @php $oc = $row['oc']; $sub = $row['sub']; @endphp
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-5 py-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $oc->grade->name_th ?? '' }} / {{ $oc->classroom->name ?? '' }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route($entryRoute, $oc->id) }}" class="text-brand-600 hover:underline">{{ $oc->course->name ?? '?' }}</a>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $oc->course->subjectGroup->name_th ?? '-' }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($sub)
                                    <x-badge :color="$sub->badgeColor()">{{ $sub->statusLabel() }}</x-badge>
                                    @if($sub->status === 'rejected' && $sub->reject_reason)
                                        <div class="text-xs text-red-400 mt-1">{{ $sub->reject_reason }}</div>
                                    @endif
                                @else
                                    <x-badge color="gray">{{ \App\Models\CourseResultSubmission::LABELS['draft'] }}</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('result-workflow.show', $oc->id) }}" class="btn-secondary text-xs py-1.5">
                                    <x-icon name="eye" class="h-4 w-4" /> {{ __('View details') }}
                                </a>
                                @forelse($row['actions'] as $action)
                                    @if($action === 'reject')
                                        <button type="button" class="btn-danger text-xs py-1.5"
                                                @click="rejectTarget = { id: {{ $oc->id }}, name: '{{ addslashes(($oc->course->name ?? '')) }}' }">
                                            {{ __('Reject') }}
                                        </button>
                                    @else
                                        <form action="{{ route('result-workflow.transition', [$oc->id, $action]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="{{ $actionMeta[$action]['class'] }} text-xs py-1.5">{{ $actionMeta[$action]['label'] }}</button>
                                        </form>
                                    @endif
                                @empty
                                    <span class="text-xs text-slate-300">—</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-slate-400 py-16">{{ __('No courses to act on') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Reject modal --}}
        <div x-show="rejectTarget !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
            <div class="absolute inset-0 bg-slate-900/50" @click="rejectTarget = null"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-1">{{ __('Reject results') }}</h3>
                <p class="text-sm text-slate-500 mb-4" x-text="rejectTarget?.name"></p>
                <form method="POST" x-bind:action="`{{ url('result-workflow') }}/${rejectTarget?.id}/reject`">
                    @csrf
                    <label class="text-xs text-slate-400">{{ __('Reason') }}</label>
                    <input type="text" name="reject_reason" maxlength="255" class="form-input text-sm mb-4" placeholder="{{ __('Reason for rejection') }}">
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn-secondary" @click="rejectTarget = null">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn-danger">{{ __('Reject') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
