@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css')}}">
@endsection

@section('content')
<div class="list-form__content">
    <h2 class="list__heading content__heading">勤怠一覧</h2>
    <div class="list__inner">
        <div class="date-display">
            @php
            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            $today = \Carbon\Carbon::now();
            @endphp
            {{ \Carbon\Carbon::now()->format('Y/m') }}
        </div>
        <table class="list__table">
            <tr class="list__row">
                <th class="list__label">日付</th>
                <th class="list__label">出勤</th>
                <th class="list__label">退勤</th>
                <th class="list__label">休憩</th>
                <th class="list__label">合計</th>
                <th class="list__label">詳細</th>
            </tr>
            @forelse($attendances as $attendance)
            <tr class="list__row">
                <td class="list__data">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d') }}</td>
                <td class="list__data">{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</td>
                <td class="list__data">@if($attendance->clock_out)
                    {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                    @else
                    -
                    @endif
                </td>


                <td class="list__data">
                    @if($attendance->breaks->isNotEmpty())
                    @foreach($attendance->breaks as $break)
                    @php
                    $start = \Carbon\Carbon::parse($break->break_start);
                    $end = $break->break_end ? \Carbon\Carbon::parse($break->break_end) : now();
                    $diffMinutes = $start->diffInMinutes($end);

                    $hours = intdiv($diffMinutes, 60);
                    $minutes = $diffMinutes % 60;
                    @endphp
                    {{ $hours }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}<br>
                    @endforeach
                    @else
                    -
                    @endif
                </td>
                <td class="list__data">
                    @if($attendance->clock_out)
                    @php
                    $clockIn = \Carbon\Carbon::parse($attendance->clock_in);
                    $clockOut = \Carbon\Carbon::parse($attendance->clock_out);
                    $workMinutes = $clockIn->diffInMinutes($clockOut);

                    // 休憩時間の合計（分）
                    $breakMinutes = $attendance->breaks->reduce(function($carry, $break) use ($clockOut) {
                    $start = \Carbon\Carbon::parse($break->break_start);
                    $end = $break->break_end ? \Carbon\Carbon::parse($break->break_end) : $clockOut;
                    return $carry + $start->diffInMinutes($end);
                    }, 0);

                    // 勤務時間から休憩時間を引く
                    $totalMinutes = $workMinutes - $breakMinutes;

                    $hours = intdiv($totalMinutes, 60);
                    $minutes = $totalMinutes % 60;
                    @endphp

                    {{ $hours }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                    @else
                    -
                    @endif
                </td>
                <td class="list__data"></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="list__data">データがありません。</td>
            </tr>
            @endforelse
        </table>
    </div>
</div>

@endsection