@extends('layouts.app')

@section('title','申請一覧画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/request-list.css') }}">
@endsection

@section('nav')
    @include('layouts._admin-nav')
@endsection

@section('content')
<div class="page-container">
    <h1 class="page__title">申請一覧</h1>

    <div class="request-list">
        <div class="request-list__header">
            <a class="request-list__header-tag {{ $status == 0 ? 'is-active' : '' }}" href="{{ route('stamp_correction_request.list', ['status' => 0, 'from' => 'admin']) }}" >承認待ち</a>
            <a class="request-list__header-tag {{ $status == 1 ? 'is-active' : '' }}" href="{{ route('stamp_correction_request.list', ['status' => 1, 'from' => 'admin']) }}" >承認済み</a>
        </div>

        <table class="request-table">
            <tr class="request-table__row">
                <th class="request-table__header">状態</th>
                <th class="request-table__header request-table__header--left">名前</th>
                <th class="request-table__header request-table__header--left">対象日時</th>
                <th class="request-table__header request-table__header--left">申請理由</th>
                <th class="request-table__header request-table__header--left">申請日時</th>
                <th class="request-table__header">詳細</th>
            </tr>
            @forelse($requests as $request)
                <tr class="request-table__row">
                    <td class="request-table__cell">{{ $request->status_label }}</td>
                    <td class="request-table__cell request-table__cell--left">{{ $request->attendance->user->name }}</td>
                    <td class="request-table__cell request-table__cell--left" >{{ $request->attendance->work_date->format('Y/m/d') }}</td>
                    <td class="request-table__cell request-table__cell--left" title="{{ $request?->reason }}">
                        {{ Str::limit($request?->reason, 12, '...') }}
                    </td>
                    <td class="request-table__cell request-table__cell--left">{{ $request->created_at->format('Y/m/d') }}</td>
                    <td class="request-table__cell">
                        <a class="request-table__cell-link" href="{{ route('admin.attendance.request.show', $request->id) }}">詳細</a>
                    </td>
                </tr>
            @empty
                <tr class="request-table__row request-table__row--empty">
                    <td class="request-table__cell request-table__no-data" colspan="6">
                        該当する申請はありません。
                    </td>
                </tr>
            @endforelse
        </table>
    </div>
</div>
@endsection
