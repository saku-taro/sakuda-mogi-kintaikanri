@extends('layouts.app')

@section('title','メール承認画面')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}" />
@endsection

@section('content')
<div class="verify-email__container">
    <p class="verify-email__message">登録していただいたメールアドレスに承認メールを送付しました。</p>
    <p class="verify-email__message">メール承認を完了してください。</p>

    <a class="verify-email__link" href="http://localhost:8025" target="_blank">認証はこちらから</a>

    <form action="{{ route('verification.send') }}" method="POST">
        @csrf
        <button class="verify-email__resend-button" type="submit">認証メールを再送する</button>
    </form>
</div>
@endsection
