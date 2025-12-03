@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h2>勤怠詳細</h2>
    <form action="{{ route('attendance.store', ['id' => $attendance->id]) }}" method="POST">
        @csrf

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ auth()->user()->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td class="editable">
                    <input type="time" name="clock_in" value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">
                    <input type="time" name="clock_out" value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">

                </td>
            </tr>
            @php
            $breakCount = count($attendance->breaks);
            @endphp

            @foreach($attendance->breaks as $index => $break)
            <tr>
                <th>休憩{{ $index + 1 }}</th>
                <td class="editable">
                    <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">
                    <input type="time" name="breaks[{{ $index }}][start]"
                        value="{{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}">
                    <input type="time" name="breaks[{{ $index }}][end]"
                        value="{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}">
                </td>
            </tr>
            @endforeach

            @for($i = $breakCount; $i < 2; $i++)
                <tr>
                <th>休憩{{ $i + 1 }}</th>
                <td class="editable">
                    <input type="time" name="breaks[{{ $i }}][start]" value="" disabled>
                    <input type="time" name="breaks[{{ $i }}][end]" value="" disabled>
                </td>
                </tr>
                @endfor




                <tr>
                    <th>備考</th>
                    <td class="editable">
                        <textarea name="reason">{{ $attendance->reason }}</textarea>
                    </td>
                </tr>
        </table>

        <button type="submit">修正</button>


    </form>







</div>
@endsection