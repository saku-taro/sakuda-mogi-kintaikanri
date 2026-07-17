@extends('layouts.app')

@section('title','マイ勤怠レポート画面（一般ユーザー）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/employee/report.css') }}">
@endsection

@section('nav')
    @include('layouts._employee-nav')
@endsection

@section('content')
<div class="page-container">
    <h1 class="page__title">マイ勤怠レポート</h1>
    <p class="page__description">過去6ヶ月の勤怠データから集計しています。</p>
    <div class="report-section">
        <h2 class="report-section__title">基本サマリー</h2>
        <div class="summary-grid">
            <div class="summary-card">
                <p class="summary-card__label">総労働時間</p>
                <p class="summary-card__value">{{ $summary['total_hours'] }}</p>
            </div>
            <div class="summary-card">
                <p class="summary-card__label">総残業時間</p>
                <p class="summary-card__value">{{ $summary['total_overtime'] }}</p>
            </div>
            <div class="summary-card">
                <p class="summary-card__label">平均労働時間/日</p>
                <p class="summary-card__value">{{ $summary['avg_daily'] }}</p>
            </div>
        </div>
    </div>
    <div class="report-section">
        <h2 class="report-section__title">月次推移(過去6ヶ月間)</h2>
        <table class="report-table">
            <tr class="report-table__row">
                <th class="report-table__header is-month">月</th>
                <th class="report-table__header is-hours">労働時間</th>
                <th class="report-table__header is-overtime">残業時間</th>
            </tr>
            @foreach($monthlyTrends as $month => $data)
                <tr class="report-table__row">
                    <td class="report-table__cell is-month">{{ $month }}</td>
                    <td class="report-table__cell is-hours">{{ $data['hours'] }}</td>
                    <td class="report-table__cell is-overtime">{{ $data['overtime'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    <div class="report-section">
        <h2 class="report-section__title">今月の異常検知</h2>
        <p class="report-section__description">基準：始業09:00/就業18:00/長時間労働は1日10時間越</p>
        <div class="anomalies-grid">
            <div class="anomaly-card">
                <p class="anomaly-card__label">遅刻回数</p>
                <p class="anomaly-card__value">{{ $anomalies['late'] }} 回</p>
            </div>
            <div class="anomaly-card">
                <p class="anomaly-card__label">早退回数</p>
                <p class="anomaly-card__value">{{ $anomalies['early_leave'] }} 回</p>
            </div>
            <div class="anomaly-card">
                <p class="anomaly-card__label">長時間労働日数</p>
                <p class="anomaly-card__value">{{ $anomalies['long_work'] }} 日</p>
            </div>
        </div>
    </div>
</div>
@endsection
