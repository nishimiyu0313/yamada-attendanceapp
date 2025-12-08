@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/approve.css') }}">
@endsection

@section('content')
<div class="approve__content">
    <div class="approve-inner">
        <h2 class="approve__heading content__heading">勤怠詳細</h2>

        <form action="{{ route('admin.stamp_request.update', $attendanceRequest->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="approve__inner">
                <table class="approve__table">
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendanceRequest->attendance->user->name ?? '未登録ユーザー' }}</td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td>{{ \Carbon\Carbon::parse($attendanceRequest->attendance->work_date)->format('Y年m月d日(D)') }}</td>
                    </tr>

                    <tr>
                        <th>出勤〜退勤</th>
                        <td>
                            {{ $attendanceRequest->requested_clock_in ? \Carbon\Carbon::parse($attendanceRequest->requested_clock_in)->format('H:i') : '-' }} 〜
                            {{ $attendanceRequest->requested_clock_out ? \Carbon\Carbon::parse($attendanceRequest->requested_clock_out)->format('H:i') : '-' }}
                        </td>
                    </tr>

                    @foreach($attendanceRequest->breaks as $index => $break)
                    <tr>
                        <th>休憩{{ $index + 1 }}</th>
                        <td>
                            {{ $break->requested_break_start ? \Carbon\Carbon::parse($break->requested_break_start)->format('H:i') : '-' }} 〜
                            {{ $break->requested_break_end ? \Carbon\Carbon::parse($break->requested_break_end)->format('H:i') : '-' }}
                        </td>
                    </tr>
                    @endforeach

                    <tr>
                        <th>備考</th>
                        <td>{{ $attendanceRequest->reason ?? '-' }}</td>
                    </tr>
                </table>

                <div class="approve__footer">
                    @if($attendanceRequest->status === 'applied')
                    <button type="approve-submit" class="btn btn-primary">承認</button>
                    @else
                    <span class="approve-submit">承認済み</span>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
@endsection