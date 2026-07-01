@extends('layouts.app')

@section('title','ログイン画面（一般ユーザー）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
@endsection

@section('content')
<form class="auth-form" action="{{ route('login') }}" method="post" novalidate>
    @csrf
    <input type="hidden" name="login_type" value="employee">

    <h1 class="auth-form__title">ログイン</h1>

    <div class="auth-form__group">
        <label class="auth-form__label">
            <span class="auth-form__label-text">メールアドレス</span>
            <input class="auth-form__input" type="email" name="email" value="{{ old('email') }}" />
        </label>
        <div class="auth-form__error">
            @error('email')
                <p class="auth-form__error-text">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="auth-form__group">
        <label class="auth-form__label">
            <span class="auth-form__label-text">パスワード</span>
            <input class="auth-form__input" type="password" name="password" />
        </label>
        <div class="auth-form__error">
            @error('password')
                <p class="auth-form__error-text">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="auth-form__actions">
        <button class="auth-form__button" type="submit">ログインする</button>
    </div>

    <div class="auth-form__footer">
        <a class="auth-form__footer-link" href="{{ route('register') }}">会員登録はこちら</a>
    </div>

</form>
@endsection
