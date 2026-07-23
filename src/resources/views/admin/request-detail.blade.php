@extends('layouts.app')

@section('title','修正申請承認画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/admin/request-detail.css') }}">
@endsection

@section('nav')
    @include('layouts._admin-nav')
@endsection

@section('content')
<div class="page-container">
    <h1 class="page__title">勤怠詳細</h1>

    <form action="{{ route('admin.attendance.request.update', $attendanceRequest->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <table class="attendance-table">
            <tr class="attendance-table__row">
                <th class="attendance-table__header">名前</th>
                <td class="attendance-table__cell attendance-table__cell--name">{{ $attendanceRequest->attendance->user->name }}</td>
            </tr>
            <tr class="attendance-table__row">
                <th class="attendance-table__header">日付</th>
                <td class="attendance-table__cell attendance-table__cell--date">
                    <span class="date-year">{{ $attendanceRequest->attendance->work_date->format('Y年') }}</span>
                    <span class="date-md">{{ $attendanceRequest->attendance->work_date->format('n月j日') }}</span>
                </td>
            </tr>
            <tr class="attendance-table__row">
                <th class="attendance-table__header">出勤・退勤</th>
                <td class="attendance-table__cell">
                    <span class="attendance-table__time">{{ $attendanceRequest->formatted_clock_in }}</span>
                    <span class="attendance-table__separator">～</span>
                    <span class="attendance-table__time">{{ $attendanceRequest->formatted_clock_out }}</span>
                </td>
            </tr>

            @foreach ($validBreaks as $index => $break)
                <tr class="attendance-table__row">
                    <th class="attendance-table__header">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                    <td class="attendance-table__cell">
                        <span class="attendance-table__time">{{ $break['break_in'] ?? '' }}</span>
                        <span class="attendance-table__separator">～</span>
                        <span class="attendance-table__time">{{ $break['break_out'] ?? '' }}</span>
                    </td>
                </tr>
            @endforeach

            <tr class="attendance-table__row">
                <th class="attendance-table__header">備考</th>
                <td class="attendance-table__cell">{{ $attendanceRequest->reason }}</td>
            </tr>
        </table>


        <div class="attendance-table__action">
<div class="attendance-table__action">
            @if ($attendanceRequest->isPending())
                <button class="attendance-table__button" type="submit">承認</button>
            @else
                <p class="attendance-table__message">承認済み</p>
            @endif
        </div>
        </div>
    </form>
</div>
@endsection
