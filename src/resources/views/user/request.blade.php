@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css')}}">
@endsection

@section('content')
<div class="list-form__content">
    <h2 class="list__heading content__heading">申請一覧</h2>
    <div class="list__inner">
        <div class="toppage-list">
            <div class="request-buttons">
                <a href="{{ route('attendance.request', ['status' => 'applied']) }}"
                    class="btn {{ $status == 'applied' ? 'active' : '' }}">承認待ち</a>

                <a href="{{ route('attendance.request', ['status' => 'approved']) }}"
                    class="btn {{ $status == 'approved' ? 'active' : '' }}">承認済み</a>
            </div>
        </div>

        <table class="list__table">
            <tr class="list__row">
                <th class="list__label">状態</th>
                <th class="list__label">名前</th>
                <th class="list__label">対象日時</th>
                <th class="list__label">申請理由</th>
                <th class="list__label">申請日時</th>
                <th class="list__label">詳細</th>
            </tr>
            @foreach ($requests as $request)
            <tr class="index__row">
                <td class="index__data">
                    @if($request->status === 'applied')
                    承認待ち
                    @elseif($request->status === 'approved')
                    承認済み
                    @endif</td>
                <td class="index__data">{{ $request->attendance->user->name ?? '-' }}</td>
                <td class="index__data">{{ $request->attendance->work_date ?? '-' }}</td>
                <td class="index__data">{{ $request->reason  }}</td>
                <td class="index__data">{{ $request->applied_date }}</td>
                <td class="index__data">
                    <a href="{{ route('attendance.detail', $request->id)  }}" class="detail-btn">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>

@endsection