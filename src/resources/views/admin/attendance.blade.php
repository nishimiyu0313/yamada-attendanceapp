@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css')}}">
@endsection

@section('content')
<div class="attendance-form__content">
    <div class="attendance__inner">
        <h2 class="attendance__heading content__heading"> {{ $staff->name }}の勤怠</h2>
        <div class="date-display">
            <div class="date-nav">
                <a href="?work_date={{ $prevDate }}" class="month-btn">← 前月</a>
                <span class="current-date">{{ $currentDate->format('Y/m') }}</span>
                <a href="?work_date={{ $nextDate }}" class="month-btn">翌月 →</a>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const input = document.getElementById('datePicker');
                    const prev = document.getElementById('prevDay');
                    const next = document.getElementById('nextDay');


                    prev.addEventListener('click', () => {
                        const d = new Date(input.value);
                        d.setDate(d.getDate() - 1);
                        input.value = d.toISOString().slice(0, 10);
                        input.dispatchEvent(new Event('change'));
                    });

                    next.addEventListener('click', () => {
                        const d = new Date(input.value);
                        d.setDate(d.getDate() + 1);
                        input.value = d.toISOString().slice(0, 10);
                        input.dispatchEvent(new Event('change'));
                    });


                    input.addEventListener('change', () => {
                        const date = input.value;
                        window.location.href = `/attendance/${date}`;
                    });
                });
            </script>


            @php
            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            $today = \Carbon\Carbon::now();
            @endphp

        </div>
        <table class="attendance__table">
            <tr class="attendance__row">
                <th class="attendance__label">日付</th>
                <th class="attendance__label">出勤</th>
                <th class="attendance__label">退勤</th>
                <th class="attendance__label">休憩</th>
                <th class="attendance__label">合計</th>
                <th class="attendance__label">詳細</th>
            </tr>

            @foreach($dates as $date)
            @php
            $key = $date->format('Y-m-d');
            $attendance = $attendances[$key] ?? null;
            $weekday = ['日','月','火','水','木','金','土'][$date->dayOfWeek];
            @endphp
            <tr>
                <td class="attendance__data">{{ $date->format('n') }}/{{ $date->format('d') }}（{{ $weekday }}）</td>
                <td class="attendance__data">{{ $attendance && $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}</td>
                <td class="attendance__data">{{ $attendance && $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}</td>
                <td class="attendance__data">
                    @if($attendance)
                    @php
                    $h = intdiv($attendance->break_minutes_total, 60);
                    $m = $attendance->break_minutes_total % 60;
                    @endphp
                    {{ $h }}:{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                    @else

                    @endif
                </td>
                <td class="attendance__data">
                    @if($attendance)
                    @php
                    $h = intdiv($attendance->work_minutes_total, 60);
                    $m = $attendance->work_minutes_total % 60;
                    @endphp
                    {{ $h }}:{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                    @else

                    @endif
                </td>
                <td class="attendance__data detail__data">
                    @if($attendance)
                    <a href=" {{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                    @else

                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        <div class="csv-area">
            <div class="csv-form">
                <form action="{{ '/csv?' . http_build_query(request()->query()) }}" method="get">
                    <input class="csv-submit" type="submit" value="CSV出力">
                </form>

            </div>
        </div>

    </div>
</div>
@endsection