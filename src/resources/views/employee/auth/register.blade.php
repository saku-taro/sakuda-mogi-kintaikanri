@extends('layouts.app')

@section('title','会員登録画面（一般ユーザー）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
@endsection

@section('content')
<form class="auth-form" action="{{ route('register') }}" method="post" novalidate>
    @csrf

    <h1 class="auth-form__title">会員登録</h1>

        <div class="auth-form__group">
        <label class="auth-form__label">
            <span class="auth-form__label-text">名前</span>
            <input class="auth-form__input" type="text" name="name" value="{{ old('name') }}" />
        </label>
        <div class="auth-form__error">
            @error('name')
                <p class="auth-form__error-text">{{ $message }}</p>
            @enderror
        </div>
    </div>

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

    <div class="auth-form__group">
        <label class="auth-form__label">
            <span class="auth-form__label-text">パスワード確認</span>
            <input class="auth-form__input" type="password" name="password_confirmation" />
        </label>
        <div class="auth-form__error">
            @error('password_confirmation')
                <p class="auth-form__error-text">{{ $message }}</p>
            @enderror
        </div>
    </div>


    <div class="auth-form__actions">
        <button class="auth-form__button" type="submit">登録する</button>
    </div>

    <div class="auth-form__footer">
        <a class="auth-form__footer-link" href="{{ route('login') }}">ログインはこちら</a>
    </div>

</form>
@endsection
