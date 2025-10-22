@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css')}}">
@endsection

@section('content')
<div class="list-form__content">
    <h2 class="list__heading content__heading">勤怠一覧</h2>
    <div class="list__inner">
        <div class="date-display">
            @php
            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            $today = \Carbon\Carbon::now();
            @endphp
            {{ \Carbon\Carbon::now()->format('Y/m') }}
        </div>
        <table class="list__table">
            <tr class="list__row">
                <th class="list__label">日付</th>
                <th class="list__label">出勤</th>
                <th class="list__label">退勤</th>
                <th class="list__label">休憩</th>
                <th class="list__label">合計</th>
                <th class="list__label">詳細</th>
            </tr>
            <tr class="list__row">
                <td class="list__data"></td>
                <td class="list__data"></td>
                <td class="list__data"></td>
                <td class="list__data"></td>
                <td class="list__data"></td>
                <td class="list__data"></td>
            </tr>
        </table>
    </div>
</div>

@endsection