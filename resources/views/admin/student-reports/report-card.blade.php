@extends('admin.student-reports.print-layout')

@php
    $student = $card['student'];
    $en = $card['enrollment'];
    $d = $card['data'] ?? null;
    $sw = $card['section_weight'] ?? null;
    $schoolName = \App\Models\Setting::query()->value('app_name') ?? config('app.name');
@endphp

@section('title', __('Report Card') . ' — ' . ($student->name_th ?? ''))

@section('body')
<style>
    .rc th, .rc td { border:1px solid #cbd5e1; padding:4px 6px; font-size:12px; }
    .rc .hd { background:#eef2ff; font-weight:700; text-align:center; }
    .mini { font-size:10px; color:#64748b; }
    .rc .num { text-align:right; }
    .rc .ctr { text-align:center; }
    .boxwrap { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px; }
</style>
<div class="ctr center" style="text-align:center;margin-bottom:8px">
    <h1>{{ $schoolName }}</h1>
    <div class="sub" style="margin:0">学生成绩通知书 · {{ __('Report Card') }} · {{ __('Academic Year') }} {{ $academicYear->year ?? '?' }} ({{ __('Semester') }} {{ $semester->semester_number ?? '?' }})</div>
</div>

<table style="width:100%;border:none;margin-bottom:6px"><tr>
    <td style="border:none">学号 / {{ __('Student Code') }}: <b>{{ $student->student_code }}</b></td>
    <td style="border:none">姓名 / {{ __('Name') }}: <b>{{ $student->name_th }} {{ $student->name_cn ? '('.$student->name_cn.')' : '' }}</b></td>
    <td style="border:none">班级 / {{ __('Class') }}: <b>{{ $en->grade->name_th ?? '-' }} / {{ $en->classroom->name ?? '-' }}</b></td>
</tr></table>

@if(!$d)
    <div class="sub">{{ __('No scores recorded yet') }}</div>
@else
@php
    $sumMid = round($d['subjects']->sum(fn($s) => $s['midterm'] ?? 0), 2);
    $sumFin = round($d['subjects']->sum(fn($s) => $s['final'] ?? 0), 2);
    $sumCol = round($d['subjects']->sum(fn($s) => $s['collect'] ?? 0), 2);
@endphp
<table class="rc" style="width:100%">
    <thead>
        <tr>
            <th class="hd" rowspan="2">科目 / {{ __('Course') }}<div class="mini">段别 {{ __('Weight') }}</div></th>
            <th class="hd" colspan="2">期中考 / {{ __('Midterm') }}</th>
            <th class="hd" colspan="2">期末考 / {{ __('Final') }}</th>
            <th class="hd" colspan="2">平时成绩 / {{ __('Collect') }}</th>
        </tr>
        <tr>
            <th class="hd">考分</th><th class="hd">比分</th>
            <th class="hd">考分</th><th class="hd">比分</th>
            <th class="hd">考分</th><th class="hd">比分</th>
        </tr>
    </thead>
    <tbody>
    @foreach($d['subjects'] as $s)
        <tr>
            <td>{{ $s['name'] }} <span class="mini">{{ $s['weight'] + 0 }}%</span></td>
            <td class="num">{{ $s['midterm'] !== null ? $s['midterm'] + 0 : '-' }}</td>
            <td class="num">{{ $s['midterm_w'] !== null ? $s['midterm_w'] + 0 : '-' }}</td>
            <td class="num">{{ $s['final'] !== null ? $s['final'] + 0 : '-' }}</td>
            <td class="num">{{ $s['final_w'] !== null ? $s['final_w'] + 0 : '-' }}</td>
            <td class="num">{{ $s['collect'] !== null ? $s['collect'] + 0 : '-' }}</td>
            <td class="num">{{ $s['collect_w'] !== null ? $s['collect_w'] + 0 : '-' }}</td>
        </tr>
    @endforeach
        <tr>
            <td class="hd">各段得分</td>
            <td class="num"><b>{{ $sumMid + 0 }}</b></td><td class="num"><b>{{ $d['mid_total'] + 0 }}</b></td>
            <td class="num"><b>{{ $sumFin + 0 }}</b></td><td class="num"><b>{{ $d['fin_total'] + 0 }}</b></td>
            <td class="num"><b>{{ $sumCol + 0 }}</b></td><td class="num"><b>{{ $d['col_total'] + 0 }}</b></td>
        </tr>
    </tbody>
</table>

<div class="boxwrap">
    {{-- Left: score summary + ranks --}}
    <div>
        <table class="rc" style="width:100%">
            <tr><td>期中考成绩 ({{ $sw->midterm_weight + 0 }}%)</td><td class="num"><b>{{ $d['mid_score'] + 0 }}</b></td>
                <td>期中考名次</td><td class="ctr">{{ $d['rank_mid'] ?? '-' }}</td></tr>
            <tr><td>期末考成绩 ({{ $sw->final_weight + 0 }}%)</td><td class="num"><b>{{ $d['fin_score'] + 0 }}</b></td>
                <td>期末考名次</td><td class="ctr">{{ $d['rank_fin'] ?? '-' }}</td></tr>
            <tr><td>平时成绩 ({{ $sw->collect_weight + 0 }}%)</td><td class="num"><b>{{ $d['col_score'] + 0 }}</b></td>
                <td>全班人数</td><td class="ctr">{{ $card['class_size'] }}</td></tr>
            <tr><td>加/扣分合计</td><td class="num">{{ $d['behavior_net'] + 0 }}</td>
                <td>学期名次</td><td class="ctr"><b>{{ $d['rank_term'] ?? '-' }}</b></td></tr>
            <tr><td class="hd">学期成绩 / {{ __('Weighted total') }}</td><td class="num hd"><b>{{ $d['term_score'] ?? '-' }}</b></td>
                <td class="hd">升/留</td><td class="ctr hd"><b>{{ $d['overall_pass'] === true ? '升' : ($d['overall_pass'] === false ? '留' : '-') }}</b></td></tr>
            <tr><td>{{ __('Overall grade') }}</td><td class="ctr"><b>{{ $d['overall_grade'] ?? '-' }}</b></td>
                <td>{{ __('Result') }}</td><td class="ctr">
                    @if($d['overall_pass'] === true)<span class="badge green">{{ __('Pass') }}</span>
                    @elseif($d['overall_pass'] === false)<span class="badge red">{{ __('Fail') }}</span>@else - @endif
                </td></tr>
        </table>
    </div>

    {{-- Right: conduct + attendance + awards --}}
    <div>
        <table class="rc" style="width:100%">
            <tr><th class="hd" colspan="2">奖惩 / {{ __('Behavior') }}</th><th class="hd" colspan="2">出勤记录 / {{ __('Attendance') }}</th></tr>
            <tr><td>嘉奖·功 (ความดี)</td><td class="num">{{ $d['merit'] > 0 ? '+'.($d['merit'] + 0) : '0' }}</td>
                <td>旷课 / {{ __('Absent') }}</td><td class="ctr">{{ $d['attendance']['absent'] }}</td></tr>
            <tr><td>过 (ความชั่ว)</td><td class="num">{{ $d['demerit'] < 0 ? ($d['demerit'] + 0) : '0' }}</td>
                <td>迟到 / {{ __('Late') }}</td><td class="ctr">{{ $d['attendance']['late'] }}</td></tr>
            <tr><td>加/扣分合计</td><td class="num"><b>{{ $d['behavior_net'] + 0 }}</b></td>
                <td>事假 / {{ __('Leave') }}</td><td class="ctr">{{ $d['attendance']['leave'] }}</td></tr>
        </table>
        <table class="rc" style="width:100%;margin-top:8px">
            <tr><th class="hd" colspan="4">操行评量 / {{ __('Conduct') }}@if($d['conduct_avg'] !== null) · {{ __('Avg') }} {{ $d['conduct_avg'] + 0 }}@endif</th></tr>
            @forelse($d['conduct']->chunk(2) as $pair)
            <tr>
                @foreach($pair as $c)
                <td>{{ $c['name_cn'] ?: $c['name'] }}</td><td class="ctr">{{ $c['score'] !== null ? $c['score'] + 0 : '-' }}</td>
                @endforeach
                @if($pair->count() < 2)<td></td><td></td>@endif
            </tr>
            @empty
            <tr><td colspan="4" class="ctr mini">{{ __('No conduct criteria defined') }}</td></tr>
            @endforelse
        </table>
    </div>
</div>

<table class="rc" style="width:100%;margin-top:10px">
    <tr><td style="width:50%">导师评语 / {{ __('Advisor comment') }}:<br><br></td><td>家长意见 / {{ __('Parent comment') }}:<br><br></td></tr>
    <tr>
        <td class="ctr mini">校长签章 &nbsp;&nbsp; 教务签章</td>
        <td class="ctr mini">训导签章 &nbsp;&nbsp; 导师签章 &nbsp;&nbsp; 家长签章</td>
    </tr>
</table>
@endif
@endsection
