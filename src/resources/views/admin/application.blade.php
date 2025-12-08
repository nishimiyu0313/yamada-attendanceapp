@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/application.css')}}">
@endsection

@section('content')
<div class="list-form__content">
    <div class="list__inner">
        <h2 class="list__heading content__heading">申請一覧</h2>

        <div class="date-display">
            <div class="request-buttons">
                <a href="{{ route('admin.requests', ['status' => 'applied']) }}"
                    class="btn {{ $status == 'applied' ? 'active' : '' }}">承認待ち</a>

                <a href="{{ route('admin.requests', ['status' => 'approved']) }}"
                    class="btn {{ $status == 'approved' ? 'active' : '' }}">承認済み</a>
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
                <tr class="list__row">
                    <td class="list__data">
                        @if($request->status === 'applied')
                        承認待ち
                        @elseif($request->status === 'approved')
                        承認済み
                        @endif</td>
                    <td class="list__data">{{ $request->attendance->user->name ?? '-' }}</td>
                    <td class="list__data">{{ $request->attendance->work_date ? \Carbon\Carbon::parse($request->attendance->work_date)->format('Y-m-d') : '-' }}</td>

                    <td class="list__data">{{ $request->reason  }}</td>
                    <td class="list__data">{{ $request->applied_date }}</td>
                    <td class="list__data detail__data">
                        <a href="{{ route('admin.stamp_request.approve', $request->id)  }}" class="detail-btn">詳細</a>
                    </td>
                </tr>
                @endforeach
            </table>

        </div>
    </div>
</div>
@endsection