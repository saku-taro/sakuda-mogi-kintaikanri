@extends('layouts.app')

@section('title','スタッフ一覧画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/admin/staff-list.css') }}">
@endsection

@section('nav')
    @include('layouts._admin-nav')
@endsection

@section('content')
<div class="page-container">
    <h1 class="page__title">スタッフ一覧</h1>

    <div class="staff-list">
            <table class="staff-table">
                <tr class="staff-table__row">
                    <th class="staff-table__header">名前</th>
                    <th class="staff-table__header">メールアドレス</th>
                    <th class="staff-table__header">月次勤怠</th>
                </tr>
                @foreach($staffs as $staff)
                    <tr class="staff-table__row">
                        <td class="staff-table__cell">{{ $staff->name }}</td>
                        <td class="staff-table__cell">{{ $staff->email }}</td>
                        <td class="staff-table__cell">
                            <a class="staff-table__cell-link" href="">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </table>
    </div>
</div>
@endsection
