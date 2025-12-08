@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-detail.css') }}">
@endsection

@section('content')
<div class="detail__content">
    <div class="attendance-inner">
        <h2 class="detail__heading content__heading">勤怠詳細</h2>

        <form action="{{ route('admin.attendance.request', $attendance->id) }}" method="POST">
            @csrf
            <div class="detail__inner">
                <table class="detail__table">
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendance->user->name }}</td>
                    </tr>

                    @php
                    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                    $today = \Carbon\Carbon::now();
                    @endphp
                    <tr>
                        <th>日付</th>
                        <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日') }}
                            ({{ $weekdays[\Carbon\Carbon::parse($attendance->work_date)->dayOfWeek] }})
                        </td>
                    </tr>

                    <tr>
                        <th>出勤〜退勤</th>
                        <td class="editable-cell">
                            <div class="time-range">
                                <input type="time" name="clock_in"
                                    value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                                    @unless($isEditable) disabled @endunless>

                                <span>～</span>

                                <input type="time" name="clock_out"
                                    value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                                    @unless($isEditable) disabled @endunless>
                            </div>
                        </td>
                    </tr>

                    @foreach($attendance->breaks as $index => $break)
                    <tr>
                        <th>休憩{{ $index + 1 }}</th>
                        <td class="editable-cell">
                            <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">
                            <div class="time-range">
                                <input type="time" name="breaks[{{ $index }}][start]"
                                    value="{{ old("breaks.$index.start", $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}"
                                    @unless($isEditable) disabled @endunless>

                                <span>～</span>

                                <input type="time" name="breaks[{{ $index }}][end]"
                                    value="{{ old("breaks.$index.end", $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}"
                                    @unless($isEditable) disabled @endunless>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    @for($i = count($attendance->breaks); $i < 2; $i++)
                        <tr>
                        <th>休憩{{ $i + 1 }}</th>
                        <td class="editable-cell">
                            <div class="time-range">
                                <input type="time" name="breaks[{{ $i }}][start]" value="" disabled>

                                <span>～</span>

                                <input type="time" name="breaks[{{ $i }}][end]" value="" disabled>
                            </div>
                        </td>
                        </tr>
                        @endfor

                        <tr>
                            <th>備考</th>
                            <td class="editable-cell">
                                <textarea name="reason" @unless($isEditable) disabled @endunless>{{ old('reason', $attendance->reason) }}</textarea>
                            </td>
                        </tr>
                </table>

                @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif

                <div class="detail__footer">
                    @if($isEditable)
                    <button type="submit" class="detail-submit">修正</button>
                    @endif
                    @if($message)
                    <div class="alert alert-warning">{{ $message }}</div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
@endsection