@extends('layouts.app')

@section('title','スタッフ別勤怠一覧画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/admin/staff-attendance-list.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('nav')
    @include('layouts._admin-nav')
@endsection

@section('content')
<div class="page-container">
    <h1 class="page__title">{{ $staff->name }}さんの勤怠</h1>

    <div class="attendance-list">
        <div class="attendance-list__header">
            <a class="attendance-list__nav-link" href="{{ route('admin.staff.attendance.list', ['id' => $staff->id, 'date' => $date->copy()->subMonth()->format('Y-m-d')]) }}">&larr;前月</a>

            {{-- 下記のコメントアウトは、月を選択するためのフォームです。 --}}
            {{-- <form action="{{ route('attendance.list') }}" method="GET" class="date-picker-form">
                <label for="month-picker" class="month-picker-label">
                    <p class="attendance-list__current-month">
                        <i class="fa-regular fa-calendar-alt"></i> {{ $date->format('Y/m') }}
                    </p>
                    <input type="month" id="month-picker" name="date" value="{{ $date->format('Y-m') }}" onchange="this.form.submit()" class="visually-hidden">
                </label>
            </form> --}}

            <p class="attendance-list__current-month">
                <i class="fa-regular fa-calendar-alt"></i> {{ $date->format('Y/m') }}
            </p>

            <a class="attendance-list__nav-link" href="{{ route('admin.staff.attendance.list', ['id' => $staff->id, 'date' => $date->copy()->addMonth()->format('Y-m-d')]) }}">翌月&rarr;</a>
        </div>

        <table class="attendance-table">
            <tr class="attendance-table__row">
                <th class="attendance-table__header request-table__header--left">日付</th>
                <th class="attendance-table__header">出勤</th>
                <th class="attendance-table__header">退勤</th>
                <th class="attendance-table__header">休憩</th>
                <th class="attendance-table__header">合計</th>
                <th class="attendance-table__header">詳細</th>
            </tr>
            @foreach($monthDays as $day)
                @php $data = $attendances->get($day->format('Y-m-d')); @endphp
                <tr class="attendance-table__row">
                    <td class="attendance-table__cell request-table__cell--left">{{ $day->format('m/d') }}({{ $day->isoFormat('ddd') }})</td>
                    <td class="attendance-table__cell">{{ $data?->clock_in?->format('H:i') ?? '' }}</td>
                    <td class="attendance-table__cell">{{ $data?->clock_out?->format('H:i') ?? '' }}</td>
                    <td class="attendance-table__cell">{{ $data ? $data->breakTotalTime : '' }}</td>
                    <td class="attendance-table__cell">{{ $data ? $data->workTime : '' }}</td>
                    <td class="attendance-table__cell">
                        @if($day->lte(now()))
                            @if($data)
                                <a class="attendance-table__cell-link" href="{{ route('admin.attendance.detail', $data->id) }}">詳細</a>
                            @else
                                <a class="attendance-table__cell-link" href="{{ route('admin.staff.attendance.create', ['date' => $day->format('Y-m-d'), 'id' => $staff->id]) }}">詳細</a>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
    <div class="btn-csv-s">
        <a class="btn-csv" href="{{ route('admin.staff.attendance.csv', ['id' => $staff->id, 'date' => $date->format('Y-m-d')]) }}">
            CSV出力
        </a>
    </div>
</div>
@endsection
