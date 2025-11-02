@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/registration.css') }}">
@endsection

@section('content')
<div class="registration-form__content">
    <div class="status-display">
        @if (!$attendance)
        勤務外
        @elseif($status === \App\Models\Attendance::STATUS_WORKING)
        出勤中
        @elseif($status === \App\Models\Attendance::STATUS_BREAKING)
        休憩中
        @elseif($attendance->clock_out)
        退勤済み
        @endif
    </div>
    <div class="date-display">
        @php
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $today = \Carbon\Carbon::now();
        @endphp
        {{ $today->format('Y年m月d日') }}（{{ $weekdays[$today->dayOfWeek] }}）
    </div>

    <div class="time-display" id="clock">
        {{ $today->format('H:i') }}
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('clock').textContent = `${hours}:${minutes}`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>


    @if (session('status'))
    <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    @if (!$attendance)
    <form class="form-btn" method="POST" action="/attendance">
        @csrf
        <button type="submit" class='registration-form__btn'>出勤</button>
    </form>

    @else

    @if($attendance && $status === \App\Models\Attendance::STATUS_WORKING)
    <form class="form-btn" method="POST" action="{{ route('break.store', $attendance->id) }}">
        @csrf
        <button type="submit" class="registration-form__btn">休憩入り</button>
    </form>
    @endif

    @if($attendance && $status === \App\Models\Attendance::STATUS_BREAKING)
    @php
    $latestBreak = $attendance->breaks()->whereNull('break_end')->latest()->first();
    @endphp
    @if($latestBreak)
    <form class="form-btn" method="POST" action="{{ route('break.update', [$attendance->id, $latestBreak->id]) }}">
        @csrf
        @method('PATCH')
        <button type="submit" class="registration-form__btn">休憩戻り</button>
    </form>
    @endif
    @endif

    <div class="attendance-footer">
        @if (!$attendance->clock_out)
        <form class="form-btn" action="{{ route('attendance.update', $attendance->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <button type="submit" class="registration-form__btn">退勤</button>
        </form>
        @else
        <span class="text-muted">お疲れ様でした。</span>
        @endif
    </div>
        @endif
        @endsection