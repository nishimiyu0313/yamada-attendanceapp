@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/registration.css')}}">
@endsection

@section('content')
<div class="registration-form__content">
    <div class="date-display">
        @php
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $today = \Carbon\Carbon::now();
        @endphp

        {{ \Carbon\Carbon::now()->format('Y年m月d日') }}（{{ $weekdays[$today->dayOfWeek] }}）
    </div>
    <div class="time-display" id="clock">
        {{ \Carbon\Carbon::now()->format('H:i') }}
    </div>
    <form class="form-btn" action="" method="post">
        <input class="registration-form__btn" type="submit" value="出勤">
    </form>
</div>
@endsection