@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css')}}">
@endsection

@section('content')
<div class="list-form__content">
    <div class="list__inner">
        <h2 class="list__heading content__heading">申請一覧</h2>
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
                <th class="list__label list__status">状態</th>
                <th class="list__label">名前</th>
                <th class="list__label">対象日時</th>
                <th class="list__label">申請理由</th>
                <th class="list__label">申請日時</th>
                <th class="list__label">詳細</th>
            </tr>
            @foreach ($requests as $request)
            <tr class="list__row">
                <td class="list__data list__status">
                    @if($request->status === 'applied')
                    承認待ち
                    @elseif($request->status === 'approved')
                    承認済み
                    @endif</td>
                <td class="list__data">{{ $request->attendance->user->name ?? '-' }}</td>
                <td class="list__data">
                    {{ $request->attendance->work_date ? \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') : '-' }}
                </td>

                <td class="list__data">{{ $request->reason  }}</td>
                <td class="list__data">{{ $request->applied_date 
        ? \Carbon\Carbon::parse($request->applied_date)->format('Y/m/d') 
        : '-' }}</td>
                <td class="list__data detail__data">
                    <a href="{{ route('attendance.detailrequest', $request->id)  }}" class="detail-btn">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection