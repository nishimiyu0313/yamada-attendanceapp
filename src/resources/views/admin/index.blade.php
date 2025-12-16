@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-index.css')}}">
@endsection

@section('content')
<div class="index-form__content">
    <div class="index__inner">
        <h2 class="index__heading content__heading"> {{ $currentDate->format('Y年m月d日') }}の勤怠</h2>

        <div class="date-display">
            <div class="date-nav">
                <a href="?work_date={{ $prevDate }}" class="btn-month">← 前日</a>
                <span class="current-date">{{ $currentDate->format('Y/m/d') }}</span>
                <a href="?work_date={{ $nextDate }}" class="btn-month">翌日 →</a>
            </div>
            <div class="date-content">
                <table class="index__table">
                    <tr class="index__row">
                        <th class="index__label">名前</th>
                        <th class="index__label">出勤</th>
                        <th class="index__label">退勤</th>
                        <th class="index__label">休憩</th>
                        <th class="index__label">合計</th>
                        <th class="index__label detail__label">詳細</th>
                    </tr>

                    @foreach ($attendances as $attendance)
                    <tr class="index__row">
                        <td class="index__data">{{ $attendance->user->name }}</td>
                        <td class="index__data">{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                        <td class="index__data">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                        <td class="index__data">{{ formatMinutes($attendance->break_minutes_total) }}</td>
                        <td class="index__data">{{ formatMinutes($attendance->work_minutes_total)  }}</td>
                        <td class="index__data detail__data">
                            <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="detail-btn">詳細</a>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>
@endsection