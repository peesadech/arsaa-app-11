@extends('admin.student-reports.print-layout')

@php
    $student = $card['student'];
    $t1 = $card['terms'][1] ?? null;
    $t2 = $card['terms'][2] ?? null;
    $d1 = $t1['data'] ?? null;
    $d2 = $t2['data'] ?? null;
    $y  = $card['year'];
    $en = $t1['enrollment'] ?? ($t2['enrollment'] ?? null);
    $schoolName = \App\Models\Setting::query()->value('app_name') ?? config('app.name');
    $cell = fn($v) => $v !== null ? $v + 0 : '-';
@endphp

@section('title', __('Report Card') . ' (' . __('Year') . ') — ' . ($student->name_th ?? ''))

@section('body')
<style>
    .rc th, .rc td { border:1px solid #cbd5e1; padding:4px 6px; font-size:12px; }
    .rc .hd { background:#eef2ff; font-weight:700; text-align:center; }
    .rc .num { text-align:right; }
    .rc .ctr { text-align:center; }
    .mini { font-size:10px; color:#64748b; }
</style>

<div style="text-align:center;margin-bottom:8px">
    <h1>{{ $schoolName }}</h1>
    <div class="sub" style="margin:0">学年成绩通知书 · {{ __('Report Card') }} ({{ __('Whole year') }}) · {{ __('Academic Year') }} {{ $academicYear->year ?? '?' }}</div>
</div>

<table style="width:100%;border:none;margin-bottom:6px"><tr>
    <td style="border:none">学号 / {{ __('Student Code') }}: <b>{{ $student->student_code }}</b></td>
    <td style="border:none">姓名 / {{ __('Name') }}: <b>{{ $student->name_th }} {{ $student->name_cn ? '('.$student->name_cn.')' : '' }}</b></td>
    <td style="border:none">班级 / {{ __('Class') }}: <b>{{ $en->grade->name_th ?? '-' }} / {{ $en->classroom->name ?? '-' }}</b></td>
</tr></table>

<table class="rc" style="width:100%">
    <thead>
        <tr>
            <th class="hd" style="text-align:left">{{ __('Item') }}</th>
            <th class="hd">上学期 / {{ __('Semester') }} 1</th>
            <th class="hd">下学期 / {{ __('Semester') }} 2</th>
            <th class="hd">学年成绩 / {{ __('Year') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>期中考成绩 / {{ __('Midterm') }}</td><td class="num">{{ $cell($d1['mid_score'] ?? null) }}</td><td class="num">{{ $cell($d2['mid_score'] ?? null) }}</td><td class="num">{{ $cell($y['mid_score']) }}</td></tr>
        <tr><td>期末考成绩 / {{ __('Final') }}</td><td class="num">{{ $cell($d1['fin_score'] ?? null) }}</td><td class="num">{{ $cell($d2['fin_score'] ?? null) }}</td><td class="num">{{ $cell($y['fin_score']) }}</td></tr>
        <tr><td>平时成绩 / {{ __('Collect') }}</td><td class="num">{{ $cell($d1['col_score'] ?? null) }}</td><td class="num">{{ $cell($d2['col_score'] ?? null) }}</td><td class="num">{{ $cell($y['col_score']) }}</td></tr>
        <tr><td class="hd" style="text-align:left">学期成绩 / {{ __('Weighted total') }}</td><td class="num hd">{{ $cell($d1['term_score'] ?? null) }}</td><td class="num hd">{{ $cell($d2['term_score'] ?? null) }}</td><td class="num hd"><b>{{ $cell($y['term_score']) }}</b></td></tr>
        <tr><td>{{ __('Overall grade') }}</td><td class="ctr">{{ $d1['overall_grade'] ?? '-' }}</td><td class="ctr">{{ $d2['overall_grade'] ?? '-' }}</td><td class="ctr"><b>{{ $y['overall_grade'] ?? '-' }}</b></td></tr>
        <tr><td>学期名次 / {{ __('Rank') }}</td><td class="ctr">{{ $d1['rank_term'] ?? '-' }}</td><td class="ctr">{{ $d2['rank_term'] ?? '-' }}</td><td class="ctr">-</td></tr>
        <tr><td>全班人数 / {{ __('Class size') }}</td><td class="ctr">{{ $t1['class_size'] ?? '-' }}</td><td class="ctr">{{ $t2['class_size'] ?? '-' }}</td><td class="ctr">-</td></tr>
    </tbody>
</table>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px">
    <table class="rc" style="width:100%">
        <tr><th class="hd" colspan="2">奖惩 / {{ __('Behavior') }} ({{ __('Whole year') }})</th></tr>
        <tr><td>嘉奖·功 (ความดี)</td><td class="num">{{ $y['merit'] > 0 ? '+'.($y['merit'] + 0) : '0' }}</td></tr>
        <tr><td>过 (ความชั่ว)</td><td class="num">{{ $y['demerit'] < 0 ? ($y['demerit'] + 0) : '0' }}</td></tr>
    </table>
    <table class="rc" style="width:100%">
        <tr><th class="hd" colspan="2">升级 / {{ __('Promotion') }}</th></tr>
        <tr><td>学年成绩</td><td class="num"><b>{{ $cell($y['term_score']) }}</b></td></tr>
        <tr><td>结果 / {{ __('Result') }}</td><td class="ctr">
            @if($y['overall_pass'] === true)<span class="badge green">升级 / {{ __('Pass') }}</span>
            @elseif($y['overall_pass'] === false)<span class="badge red">留级 / {{ __('Fail') }}</span>@else - @endif
        </td></tr>
    </table>
</div>

<table class="rc" style="width:100%;margin-top:10px">
    <tr><td style="width:50%">导师评语 / {{ __('Advisor comment') }}:<br><br></td><td>家长意见 / {{ __('Parent comment') }}:<br><br></td></tr>
    <tr><td class="ctr mini">校长签章 &nbsp; 教务签章</td><td class="ctr mini">训导签章 &nbsp; 导师签章 &nbsp; 家长签章</td></tr>
</table>
@endsection
