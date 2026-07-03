@extends('admin.student-reports.print-layout')

@section('title', __('Transcript') . ' — ' . $student->name_th)

@section('body')
<h1>{{ __('Transcript') }}</h1>
<div class="sub">{{ __('Student Code') }}: <b>{{ $student->student_code }}</b> · {{ $student->name_th }} {{ $student->name_cn ? '(' . $student->name_cn . ')' : '' }}</div>

@if($scores->isEmpty())
<div class="sub">{{ __('No scores recorded yet') }}</div>
@else
@foreach($scores as $term => $termScores)
<h2>{{ __('Academic Year') }} / {{ __('Semester') }}: {{ $term }} — {{ $termScores->first()->openedCourse->grade->name_th ?? '' }} / {{ $termScores->first()->openedCourse->classroom->name ?? '' }}</h2>
<table>
    <thead>
        <tr>
            <th>{{ __('Course') }}</th>
            <th>{{ __('Subject Group') }}</th>
            <th>{{ __('Teacher') }}</th>
            <th class="right">{{ __('Collect') }}</th>
            <th class="right">{{ __('Midterm') }}</th>
            <th class="right">{{ __('Final') }}</th>
            <th class="right">{{ __('Total') }}</th>
            <th class="center">{{ __('Grade') }}</th>
            <th class="center">{{ __('Result') }}</th>
        </tr>
    </thead>
    <tbody>
    @foreach($termScores as $score)
        <tr>
            <td>{{ $score->openedCourse->course->name ?? '?' }}</td>
            <td>{{ $score->openedCourse->course->subjectGroup->name_th ?? '-' }}</td>
            <td>{{ $score->teacher->name ?? '-' }}</td>
            <td class="right">{{ $score->score_collect ?? '-' }}</td>
            <td class="right">{{ $score->score_midterm ?? '-' }}</td>
            <td class="right">{{ $score->score_final ?? '-' }}</td>
            <td class="right"><b>{{ $score->total_score ?? '-' }}</b></td>
            <td class="center"><b>{{ $score->grade ?? '-' }}</b></td>
            <td class="center">
                @if($score->result_status === 'pass')<span class="badge green">{{ __('Pass') }}</span>
                @elseif($score->result_status === 'fail')<span class="badge red">{{ __('Fail') }}</span>
                @else - @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endforeach
@endif
@endsection
