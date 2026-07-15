@extends('layouts.app')

@section('title','勤怠詳細画面（一般ユーザー）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/employee/attendance-detail.css') }}">
@endsection

@section('nav')
    @include('layouts._employee-nav')
@endsection

@section('content')
<div class="page-container">
    <h1 class="page__title">勤怠詳細</h1>

    {{-- @if($attendanceRequest) を削除し、常にフォームを表示する --}}
    <form action="{{ isset($attendance) ? route('attendance.request.update', $attendance->id) : route('attendance.request.store', ['date' => $date]) }}" method="POST">
        @csrf
        @if(isset($attendance)) @method('PATCH') @endif
        <input type="hidden" name="work_date" value="{{ $date->format('Y-m-d') }}">

        {{-- 申請があるなら編集不可(false)、なければ編集可能(true) --}}
        @include('layouts._attendance-table', ['isEditable' => !$attendanceRequest])

        <div class="attendance-table__action">
            {{-- ここで承認待ちかどうかを判定 --}}
            @if($attendanceRequest && $attendanceRequest->isPending())
                <p class="attendance-table__message">※承認待ちのため修正できません。</p>
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
