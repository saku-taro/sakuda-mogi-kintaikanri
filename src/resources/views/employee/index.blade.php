@extends('layouts.app')

@section('title','勤怠登録画面（一般ユーザー）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/employee/index.css') }}">
@endsection

@section('nav')
    @include('layouts._employee-nav')
@endsection

@section('content')
<div class="attendance-container">
    <p class="status-text">
        @if($attendance && $attendance->isResting())
            休憩中
        @elseif($isFinished)
            退勤済
        @elseif($attendance)
            出勤中
        @else
            勤務外
        @endif
    </p>
    <p class="current-date">{{ $dateString }} ({{ $dayOfWeek }})</p>
    <p class="current-time">{{ $currentTime }}</p>

    @if($attendance)
        @if($isResting)
            <div class="button-group">
                <form action="{{ route('break.update') }}" method="POST">
                    @csrf
                    <button class="attendance-button rest-button" type="submit">休憩戻</button>
                </form>
            </div>
            @else
                <div class="button-group">
                    <form action="{{ route('attendance.update') }}" method="POST">
                        @csrf
                        <button class="attendance-button" type="submit">退勤</button>
                    </form>
                    <form action="{{ route('break.store') }}" method="POST">
                        @csrf
                        <button class="attendance-button rest-button" type="submit">休憩入</button>
                    </form>
                </div>
        @endif

    @elseif($isFinished)
        <p class="finished-text">お疲れ様でした。</p>

    @else
        <div class="button-group">
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <button class="attendance-button" type="submit">出勤</button>
            </form>
        </div>
    @endif
</div>
@endsection
