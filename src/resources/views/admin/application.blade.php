@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/application.css')}}">
@endsection

@section('content')
<div class="list-form__content">
    <h2 class="list__heading content__heading">申請一覧</h2>
    <div class="list__inner">
        <div class="date-display">
            <div class="request-buttons">
                <a href="{{ route('admin.requests', ['status' => 'pending']) }}"
                    class="btn {{ $status == 'pending' ? 'active' : '' }}">承認待ち</a>

                <a href="{{ route('admin.requests', ['status' => 'approved']) }}"
                    class="btn {{ $status == 'approved' ? 'active' : '' }}">承認済み</a>
            </div>
            <table class="index__table">
                <tr class="index__row">
                    <th class="index__label">状態</th>
                    <th class="index__label">名前</th>
                    <th class="index__label">対象日時</th>
                    <th class="index__label">申請理由</th>
                    <th class="index__label">申請日時</th>
                    <th class="index__label">詳細</th>

                </tr>
                @foreach ($requests as $request)
                <tr class="index__row">
                    <td class="index__data">{{ $request->status }}</td>
                    <td class="index__data">{{ $request->user->name ?? '-'  }}</td>
                    <td class="index__data">{{ $request->attendance->work_date ?? '-' }}</td>
                    <td class="index__data">{{ $request->reason  }}</td>
                    <td class="index__data">{{ $request->applied_date }}</td>
                    <td class="index__data">
                        <a href="{{ route('admin.stamp_request.approve', $request->id)  }}" class="detail-btn">詳細</a>
                    </td>
                </tr>
                @endforeach
            </table>

        </div>
    </div>
</div>
@endsection