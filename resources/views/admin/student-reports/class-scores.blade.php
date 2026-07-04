@extends('admin.student-reports.print-layout')

@section('title', __('Class scores report'))

@section('body')
@php $first = $openedCourses->first(); @endphp
<h1>{{ __('Class scores report') }}</h1>
<div class="sub">
    {{ __('Academic Year') }} {{ $first?->academicYear->year ?? '-' }} / {{ __('Semester') }} {{ $first?->semester->semester_number ?? '-' }}
    · {{ $first?->grade->name_th ?? '' }} / {{ $first?->classroom->name ?? '' }}
</div>

@if($enrollments->isEmpty())
<div class="sub">{{ __('No students enrolled in this classroom yet') }}</div>
@else
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('Student Code') }}</th>
            <th>{{ __('Name') }}</th>
            @foreach($openedCourses as $oc)
            <th class="center">{{ $oc->course->name ?? '?' }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
    @foreach($enrollments as $i => $enrollment)
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td>{{ $enrollment->student->student_code ?? '?' }}</td>
            <td>{{ $enrollment->student->name_th ?? '?' }}@if($enrollment->student->name_cn ?? false)<br><span style="color:#666">{{ $enrollment->student->name_cn }}</span>@endif</td>
            @foreach($openedCourses as $oc)
                @php $score = $scores->get($enrollment->student_id . '-' . $oc->id); @endphp
                <td class="center">
                    @if($score && $score->total_score !== null)
                        {{ $score->total_score + 0 }} <b>({{ $score->grade ?? '-' }})</b>
                    @else - @endif
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
@endif
@endsection
