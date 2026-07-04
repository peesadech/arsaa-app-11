@extends('admin.student-reports.print-layout')

@section('title', __('Students with incomplete documents'))

@section('body')
<h1>{{ __('Students with incomplete documents') }}</h1>
<div class="sub">{{ __('Studying students whose received documents are fewer than :total required types', ['total' => $totalTypes]) }}</div>

@if($students->isEmpty())
<div class="sub">{{ __('All students have complete documents') }} ✓</div>
@else
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('Student Code') }}</th>
            <th>{{ __('Name') }}</th>
            <th class="center">{{ __('Received') }}</th>
            <th class="center">{{ __('Missing') }}</th>
            <th>{{ __('Missing documents') }}</th>
        </tr>
    </thead>
    <tbody>
    @foreach($students as $i => $student)
        @php
            $receivedTypeIds = $student->documents->where('is_received', true)->pluck('document_type_id');
            $missing = $documentTypes->reject(fn($t) => $receivedTypeIds->contains($t->id));
        @endphp
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td>{{ $student->student_code }}</td>
            <td>{{ $student->name_th }}@if($student->name_cn)<br><span style="color:#666">{{ $student->name_cn }}</span>@endif</td>
            <td class="center"><span class="badge green">{{ $student->received_count }}/{{ $totalTypes }}</span></td>
            <td class="center"><span class="badge red">{{ $student->missing_count }}</span></td>
            <td>{{ $missing->pluck('name_th')->join(', ') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif
@endsection
