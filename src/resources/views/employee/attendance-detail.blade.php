@extends('layouts.app')

@section('title','勤怠詳細画面（一般ユーザー）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/attendance-detail.css') }}">
@endsection

@section('nav')
    @include('layouts._employee-nav')
@endsection

@section('content')
<div class="page-container">
    <h1 class="page__title">勤怠詳細</h1>

    <form action="{{ isset($attendance) ? route('attendance.request.update', $attendance->id) : route('attendance.request.store', ['date' => $date]) }}" method="POST">
        @csrf
        @if(isset($attendance)) @method('PATCH') @endif
        <input type="hidden" name="work_date" value="{{ $date->format('Y-m-d') }}">

        @include('layouts._attendance-table', ['isEditable' => $isEditable && !$attendanceRequest])

        <div class="attendance-table__action">
            @if($attendanceRequest && $attendanceRequest->isPending())
                <p class="attendance-table__message">※承認待ちのため修正はできません。</p>
            @elseif(!$isEditable)
                <p class="attendance-table__message">※本日および未来の日付は修正申請できません。</p>
            @else
                <button class="attendance-table__button" type="submit">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const timeInputs = document.querySelectorAll('.attendance-table__input');

            timeInputs.forEach(input => {
                const checkValue = () => {
                    if (input.value === '') {
                        input.classList.add('is-empty');
                    } else {
                        input.classList.remove('is-empty');
                    }
                };

                input.addEventListener('input', checkValue);

                checkValue();
            });
        });
    </script>
@endsection
