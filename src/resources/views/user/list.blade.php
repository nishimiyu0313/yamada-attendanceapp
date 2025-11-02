@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css')}}">
@endsection

@section('content')
<div class="list-form__content">
    <h2 class="list__heading content__heading">勤怠一覧</h2>
    <div class="list__inner">
        <div class="date-display">
            <div class="date-selector">
                <button id="prevDay" class="date-btn">←前月</button>

               
                <div class="date-center">
                    <i class="fa-solid fa-calendar-days"></i>
                    <input
                        type="date"
                        id="datePicker"
                        value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                        class="date-input" />
                </div>

                
                <button id="nextDay" class="date-btn">翌月→</button>
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
                <td class="list__data">{{ \Carbon\Carbon::parse($attendance->work_date)
                     ->locale('ja')     
                     ->isoFormat('MM/DD(ddd)')
                }}
                </td>
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

                   
                    $breakMinutes = $attendance->breaks->reduce(function($carry, $break) use ($clockOut) {
                    $start = \Carbon\Carbon::parse($break->break_start);
                    $end = $break->break_end ? \Carbon\Carbon::parse($break->break_end) : $clockOut;
                    return $carry + $start->diffInMinutes($end);
                    }, 0);

                    
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