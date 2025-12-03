@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/approve.css') }}">
@endsection

@section('content')
<div class="detail__content">
    <h2 class="detail__heading content__heading">勤怠詳細</h2>

    <form action="{{ route('admin.stamp_request.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="detail__inner">
            <table class="detail__table">
                <tr>
                    <th>名前</th>
                    <td>{{ $attendance->user->name ?? '未登録ユーザー' }}</td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日(D)') }}</td>
                </tr>

                <tr>
                    <th>出勤〜退勤</th>
                    <td class="editable-cell">
                        <input type="time" name="clock_in" value="{{ $attendanceRequest->requested_clock_in ? \Carbon\Carbon::parse($attendanceRequest->requested_clock_in)->format('H:i') : '' }}"> 〜
                        <input type="time" name="clock_out" value="{{ $attendanceRequest->requested_clock_out ? \Carbon\Carbon::parse($attendanceRequest->requested_clock_out)->format('H:i') : '' }}">
                    </td>
                </tr>

                <tr>
                <tr>
                    <th>休憩1</th>
                    <td class=" editable-cell">
                        <input type="time" name="break1_start" value="{{ isset($attendanceRequest->breaks[0]) ? \Carbon\Carbon::parse($attendanceRequest->breaks[0]->requested_start)->format('H:i') : '' }}"> 〜
                        <input type="time" name="break1_end" value="{{ isset($attendanceRequest->breaks[0]) && $attendanceRequest->breaks[0]->requested_end ? \Carbon\Carbon::parse($attendanceRequest->breaks[0]->requested_end)->format('H:i') : '' }}">
                        <input type="hidden" name="break1_id" value="{{ $attendanceRequest->breaks[0]->id ?? '' }}">
                    </td>
                </tr>

                <tr>
                    <th>休憩2</th>
                    <td class="editable-cell">
                        <input type="time" name="break2_start" value="{{ isset($attendanceRequest->breaks[1]) ? \Carbon\Carbon::parse($attendanceRequest->breaks[1]->requested_start)->format('H:i') : '' }}"> 〜
                        <input type="time" name="break2_end" value="{{ isset($attendanceRequest->breaks[1]) && $attendanceRequest->breaks[1]->requested_end ? \Carbon\Carbon::parse($attendanceRequest->breaks[1]->requested_end)->format('H:i') : '' }}">
                        <input type="hidden" name="break2_id" value="{{ $attendanceRequest->breaks[1]->id ?? '' }}">
                    </td>
                </tr>


                <tr>
                    <th>備考</th>
                    <td class="editable-cell">
                        <textarea name="reason" rows="3">{{ $attendanceRequest->reason ?? ''  }}</textarea>
                    </td>
                </tr>
            </table>

            <div class="detail__footer">
                @if($attendanceRequest->status === 'applied')
                <button type="submit">承認</button>
                @else
                <span class="approved-label">承認済み</span>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection