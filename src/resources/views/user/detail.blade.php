@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <div class="attendance-inner">
        <h2 class="detail__heading content__heading">勤怠詳細</h2>

        <form action="{{ route('attendance.detailstore', ['id' => $attendance->id]) }}" method="POST" novalidate>
            @csrf
            <div class="detail__content">
                <table class="detail__table">
                    <tr>
                        <th>名前</th>
                        <td>{{ auth()->user()->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日') }}</td>
                    </tr>
                    @php
                    $requestData = $attendance->requests()->latest()->first();
                    @endphp
                    <tr>
                        <th>出勤・退勤</th>
                        <td class="editable">
                            <div class="time-range">
                                <input type="time" name="clock_in"
                                    value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                                    @unless($isEditable) disabled @endunless>

                                <span>～</span>

                                <input type="time" name="clock_out"
                                    value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                                    @unless($isEditable) disabled @endunless>
                                <div class="register-form__error-message">
                                    {{ $errors->first('clock_in') }}
                                    {{ $errors->first('clock_out') }}
                                </div>
                            </div>
                        </td>
                    </tr>

                    @php $breakCount = count($attendance->breaks); @endphp

                    @foreach($attendance->breaks as $index => $break)
                    <tr>
                        <th>休憩{{ $index + 1 }}</th>
                        <td class="editable">
                            <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">

                            <div class="time-range">
                                <input type="time" name="breaks[{{ $index }}][start]"
                                    value="{{ old("breaks.$index.start", $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}"
                                    @unless($isEditable) disabled @endunless>

                                <span>～</span>

                                <input type="time" name="breaks[{{ $index }}][end]"
                                    value="{{ old("breaks.$index.end", $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}"
                                    @unless($isEditable) disabled @endunless>
                                <div class="register-form__error-message">
                                    {{ $errors->first('breaks.*.start') }}
                                    {{ $errors->first('breaks.*.end') }}
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <th>休憩{{ $breakCount + 1 }}</th>
                        <td class="editable">
                            <div class="time-range">
                                <input type="time" name="breaks[{{ $breakCount }}][start]" value="" @unless($isEditable) disabled @endunless>

                                <span>～</span>

                                <input type="time" name="breaks[{{ $breakCount }}][end]" value="" @unless($isEditable) disabled @endunless>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td class="editable">
                            <textarea name="reason" @unless($isEditable) disabled @endunless>{{ $attendance->reason }}</textarea>
                            <div class="register-form__error-message">
                                {{ $errors->first('reason') }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            @if (session('error'))
            <div class="alert-danger">
                {{ session('error') }}
            </div>
            @endif

            @if($isEditable)
            <button type="submit" class="detail-submit">修正</button>
            @endif
            @if($message)
            <div class="alert-warning">{{ $message }}</div>
            @endif
        </form>
    </div>
</div>
@endsection