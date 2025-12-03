@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css')}}">
@endsection

@section('content')
<div class="index-form__content">
    <h2 class="index__heading content__heading"> {{ $staff->name }}の勤怠</h2>
    <div class="index__inner">
        <div class="date-display">
            <div class="date-nav">
                <a href="?work_date={{ $prevDate }}" class="btn">← 前月</a>
                <span class="current-date">{{ $currentDate->format('Y/m/') }}</span>
                <a href="?work_date={{ $nextDate }}" class="btn">翌月 →</a>
            </div>
            <div class="date-content">
                <table class="index__table">
                    <tr class="index__row">
                        <th class="index__label">日付</th>
                        <th class="index__label">出勤</th>
                        <th class="index__label">退勤</th>
                        <th class="index__label">休憩</th>
                        <th class="index__label">合計</th>
                        <th class="index__label">詳細</th>
                    </tr>

                    @foreach($dates as $date)
                    @php
                    $key = $date->format('Y-m-d');
                    $attendance = $attendances[$key] ?? null;
                    $weekday = ['日','月','火','水','木','金','土'][$date->dayOfWeek];
                    @endphp

                    <tr class="index__row">
                        <td class="index__data">{{ $date->format('n') }}/{{ $date->format('d') }} ({{ $weekday }})</td>
                        <td class="index__data"> {{ $attendance && $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}</td>
                        <td class="index__data"> {{ $attendance && $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}</td>
                        <td class="index__data">
                            @if($attendance)
                            @php
                            $h = intdiv($attendance->break_minutes_total, 60);
                            $m = $attendance->break_minutes_total % 60;
                            @endphp
                            {{ $h }}:{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="index__data">
                            @if($attendance)
                            @php
                            $h = intdiv($attendance->work_minutes_total, 60);
                            $m = $attendance->work_minutes_total % 60;
                            @endphp
                            {{ $h }}:{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="index__data">
                            @if($attendance)
                            <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="detail-btn">詳細</a>
                            @else
                            -
                            @endif
                        </td>
                    </tr>

                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>
@endsection