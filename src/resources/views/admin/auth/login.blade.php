@extends('layouts.app')

@section('title','ログイン画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/authentication.css')  }}">
@endsection

@section('content')
<form class="auth-form" action="{{ route('login') }}" method="post" novalidate>
    @csrf
    <input type="hidden" name="login_type" value="admin">

    <h1 class="auth-form__title">管理者ログイン</h1>

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
        <button class="auth-form__button" type="submit">管理者ログインする</button>
    </div>

</form>
@endsection
