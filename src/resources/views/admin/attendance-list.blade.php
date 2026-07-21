@extends('layouts.app')

@section('title','勤怠一覧画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/admin/attendance-list.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('nav')
    @include('layouts._admin-nav')
@endsection

@section('content')
<div class="page-container">
    <h1 class="page__title">{{ $date->format('Y年n月j日') }} の勤怠一覧</h1>

    <div class="attendance-list">
        <div class="attendance-list__header">
            <a class="attendance-list__nav-link" href="{{ route('admin.index', ['date' => $date->copy()->subDay()->toDateString()]) }}">&larr;前日</a>

            {{-- <form action="{{ route('admin.index') }}" method="GET" class="date-picker-form">
                <label for="month-picker" class="month-picker-label">
                    <p class="attendance-list__current-month">
                        <i class="fa-regular fa-calendar-alt"></i> {{ $date->format('Y/m') }}
                    </p>
                    <!-- name="date" で日付を送信 -->
                    <input type="month" id="month-picker" name="date" value="{{ $date->format('Y-m') }}" onchange="this.form.submit()" class="visually-hidden">
                </label>
            </form> --}}

            <p class="attendance-list__current-date">
                <i class="fa-regular fa-calendar-alt"></i> {{ $date->format('Y/m/d') }}
            </p>

            <a class="attendance-list__nav-link" href="{{ route('admin.index', ['date' => $date->copy()->addDay()->toDateString()]) }}">翌日&rarr;</a>
        </div>

            <table class="attendance-table">
                <tr class="attendance-table__row">
                    <th class="attendance-table__header">名前</th>
                    <th class="attendance-table__header">出勤</th>
                    <th class="attendance-table__header">退勤</th>
                    <th class="attendance-table__header">休憩</th>
                    <th class="attendance-table__header">合計</th>
                    <th class="attendance-table__header">詳細</th>
                </tr>
                @foreach($attendances as $attendance)
                    <tr class="attendance-table__row">
                        <td class="attendance-table__cell">{{ $attendance->user->name }}</td>
                        <td class="attendance-table__cell">{{ $attendance?->clock_in?->format('H:i') }}</td>
                        <td class="attendance-table__cell">{{ $attendance?->clock_out?->format('H:i') }}</td>
                        <td class="attendance-table__cell">{{ $attendance?->break_total_time }}</td>
                        <td class="attendance-table__cell">{{ $attendance?->work_time }}</td>
                        <td class="attendance-table__cell">
                            @if(!$attendance->isFuture())
                                <a class="attendance-table__cell-link" href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
    </div>
</div>
@endsection
