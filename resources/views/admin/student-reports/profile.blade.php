@extends('admin.student-reports.print-layout')

@section('title', __('Student Profile') . ' — ' . $student->name_th)

@section('body')
@php
    $guardianStatusLabels = ['alive' => __('Alive'), 'deceased' => __('Deceased'), 'together' => __('Living together'), 'divorced' => __('Divorced'), 'other' => __('Other')];
    $statusLabels = ['studying' => __('Studying'), 'suspended' => __('Suspended'), 'resigned' => __('Resigned'), 'graduated' => __('Graduated')];
@endphp

<div style="display:flex; justify-content:space-between; gap:16px;">
    <div>
        <h1>{{ __('Student Profile') }}</h1>
        <div class="sub">{{ __('Student Code') }}: <b>{{ $student->student_code }}</b> · {{ __('Status') }}: {{ $statusLabels[$student->status] ?? $student->status }}</div>
    </div>
    @if($student->image_path)
    <img src="{{ asset($student->image_path) }}" class="photo" alt="">
    @endif
</div>

<h2>{{ __('General Info') }}</h2>
<div class="grid">
    <div class="field"><b>{{ __('Name (TH)') }}</b>{{ $student->name_th }}</div>
    <div class="field"><b>{{ __('Chinese Name') }}</b>{{ $student->name_cn ?: '-' }}</div>
    <div class="field"><b>{{ __('Citizen ID') }}</b>{{ $student->citizen_id ?: '-' }}</div>
    <div class="field"><b>{{ __('Birth Date') }}</b>{{ $student->birth_date?->format('d/m/Y') ?: '-' }} ({{ $student->age !== null ? $student->age . ' ' . __('years old') : '-' }})</div>
    <div class="field"><b>{{ __('Race') }}</b>{{ $student->race->name_th ?? '-' }}</div>
    <div class="field"><b>{{ __('Nationality') }}</b>{{ $student->nationality->name_th ?? '-' }}</div>
    <div class="field"><b>{{ __('Religion') }}</b>{{ $student->religion->name_th ?? '-' }}</div>
    <div class="field"><b>{{ __('Blood Type') }}</b>{{ $student->bloodType->name_th ?? '-' }}</div>
    <div class="field"><b>{{ __('Height (cm)') }}</b>{{ $student->height ?: '-' }}</div>
    <div class="field"><b>{{ __('Weight (kg)') }}</b>{{ $student->weight ?: '-' }}</div>
    <div class="field"><b>{{ __('Chronic Disease') }}</b>{{ $student->chronic_disease ?: '-' }}</div>
    <div class="field"><b>{{ __('Home Phone') }} / {{ __('Mobile') }}</b>{{ $student->phone ?: '-' }} / {{ $student->mobile ?: '-' }}</div>
</div>

<h2>{{ __('Address') }}</h2>
<div class="grid">
    <div class="field"><b>{{ __('Current Address') }}</b>{{ $student->addressOfType('current')?->full_address ?: '-' }}</div>
    <div class="field"><b>{{ __('Registered Address') }}</b>{{ $student->addressOfType('registered')?->full_address ?: '-' }}</div>
</div>

<h2>{{ __('Guardians') }}</h2>
@if($student->guardians->isEmpty())
<div class="sub">-</div>
@else
<table>
    <thead>
        <tr>
            <th>{{ __('Guardian Type') }}</th><th>{{ __('Name') }}</th><th>{{ __('Age') }}</th>
            <th>{{ __('Living Status') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Occupation') }}</th><th>{{ __('Primary guardian') }}</th>
        </tr>
    </thead>
    <tbody>
    @foreach($student->guardians as $g)
        <tr>
            <td>{{ $g->guardianType->name_th ?? '-' }}</td>
            <td>{{ $g->name }} {{ $g->name_cn ? '(' . $g->name_cn . ')' : '' }}</td>
            <td class="center">{{ $g->age ?: '-' }}</td>
            <td>{{ $guardianStatusLabels[$g->living_status] ?? '-' }}</td>
            <td>{{ $g->phone ?: '-' }}</td>
            <td>{{ $g->occupation ?: '-' }}</td>
            <td class="center">{{ $g->is_primary ? '✓' : '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

<h2>{{ __('Education History') }}</h2>
@if($student->educationHistories->isEmpty())
<div class="sub">-</div>
@else
<table>
    <thead><tr><th>{{ __('School Name') }}</th><th>{{ __('School Location') }}</th><th>{{ __('Last Level Completed') }}</th><th>{{ __('GPA') }}</th><th>{{ __('Graduated (month/year)') }}</th></tr></thead>
    <tbody>
    @foreach($student->educationHistories as $edu)
        <tr>
            <td>{{ $edu->school_name }}</td><td>{{ $edu->school_location ?: '-' }}</td>
            <td>{{ $edu->last_level ?: '-' }}</td><td class="center">{{ $edu->gpa ?: '-' }}</td><td class="center">{{ $edu->graduated_at ?: '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

<h2>{{ __('Application Documents Checklist') }}</h2>
<table>
    <thead><tr><th>{{ __('Document') }}</th><th class="center">{{ __('Received') }}</th><th>{{ __('Note') }}</th></tr></thead>
    <tbody>
    @foreach($documentTypes as $type)
        @php $doc = $student->documents->firstWhere('document_type_id', $type->id); @endphp
        <tr>
            <td>{{ $type->name_th }}</td>
            <td class="center">{!! $doc?->is_received ? '<span class="badge green">✓</span>' : '<span class="badge red">✗</span>' !!}</td>
            <td>{{ $doc->note ?? '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>{{ __('Classroom History') }}</h2>
@if($student->enrollments->isEmpty())
<div class="sub">-</div>
@else
<table>
    <thead><tr><th>{{ __('Academic Year') }}</th><th>{{ __('Semester') }}</th><th>{{ __('Grade Level') }} / {{ __('Classroom') }}</th><th>{{ __('Status') }}</th><th>{{ __('Enrolled date') }}</th></tr></thead>
    <tbody>
    @foreach($student->enrollments->sortByDesc('id') as $e)
        <tr>
            <td class="center">{{ $e->academicYear->year ?? '-' }}</td>
            <td class="center">{{ $e->semester->semester_number ?? '-' }}</td>
            <td>{{ $e->grade->name_th ?? '' }} / {{ $e->classroom->name ?? '' }}</td>
            <td class="center">{{ ['enrolled' => __('Enrolled'), 'moved' => __('Moved'), 'left' => __('Left')][$e->status] ?? $e->status }}</td>
            <td class="center">{{ $e->enrolled_at?->format('d/m/Y') ?: '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif
@endsection
