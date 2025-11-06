@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h2>勤怠詳細</h2>
    <form action="{{ route('attendance.update', ['id' => $attendance->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ auth()->user()->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td class="editable">
                    <input type="time" name="clock_in" value="{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}">
                    ～
                    <input type="time" name="clock_out" value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
                </td>
            </tr>
            <tr>
                <th>休憩</th>
                <td class="editable">
                    @foreach($attendance->breaks as $i => $break)
                    <input type="time" name="breaks[{{ $i }}][start]" value="{{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}">
                    ～
                    <input type="time" name="breaks[{{ $i }}][end]" value="{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}">
                    <br>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td class="editable">
                    <textarea name="note">{{ $attendance->note }}</textarea>
                </td>
            </tr>
        </table>
        <button type="submit">修正</button>
    </form>
</div>
@endsection