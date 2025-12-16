@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stafflist.css')}}">
@endsection

@section('content')
<div class="list-form__content">
    <div class="list__inner">
        <h2 class="list__heading content__heading">スタッフ一覧</h2>
        <div class="date-display">
            <table class="index__table">
                <tr class="index__row">
                    <th class="index__label">名前</th>
                    <th class="index__label">メールアドレス</th>
                    <th class="index__label">月次勤務</th>
                </tr>
                @foreach ($users as $user)
                <tr class="index__row">
                    <td class="index__data">{{ $user->name }}</td>
                    <td class="index__data">{{ $user->email }}</td>
                    <td class="index__data detail__data">
                        <a href="{{ route('admin.attendance.staff', $user->id) }}" class="detail-btn">詳細</a>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection