@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="detail__content">
    <h2 class="detail__heading content__heading">勤怠詳細</h2>

    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="detail__inner">
            <table class="detail__table">
                <tr>
                    <th>名前</th>
                    <td>{{ $attendance->user->name }}</td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日(D)') }}</td>
                </tr>

                <tr>
                    <th>出勤〜退勤</th>
                    <td class="editable-cell">
                        <input type="time" name="clock_in" value="{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}"> 〜
                        <input type="time" name="clock_out" value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
                    </td>
                </tr>

                <tr>
                    <th>休憩1</th>
                    <td class="editable-cell">
                        <input type="time" name="break1_start" value="{{ isset($attendance->breaks[0]) ? \Carbon\Carbon::parse($attendance->breaks[0]->break_start)->format('H:i') : '' }}"> 〜
                        <input type="time" name="break1_end" value="{{ isset($attendance->breaks[0]) && $attendance->breaks[0]->break_end ? \Carbon\Carbon::parse($attendance->breaks[0]->break_end)->format('H:i') : '' }}">
                    </td>
                </tr>

                <tr>
                    <th>休憩2</th>
                    <td class="editable-cell">
                        <input type="time" name="break2_start" value="{{ isset($attendance->breaks[1]) ? \Carbon\Carbon::parse($attendance->breaks[1]->break_start)->format('H:i') : '' }}"> 〜
                        <input type="time" name="break2_end" value="{{ isset($attendance->breaks[1]) && $attendance->breaks[1]->break_end ? \Carbon\Carbon::parse($attendance->breaks[1]->break_end)->format('H:i') : '' }}">
                    </td>
                </tr>

                <tr>
                    <th>備考</th>
                    <td class="editable-cell">
                        <textarea name="note" rows="3">{{ $attendance->note ?? '' }}</textarea>
                    </td>
                </tr>
            </table>

            <div class="detail__footer">
                <button type="submit" class="save-btn">変更を保存</button>
                <a href="{{ url()->previous() }}" class="back-btn">← 一覧に戻る</a>
            </div>
        </div>
    </form>
</div>
@endsection