@extends('layouts.app')

@section('head-title', 'ログイン画面')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
    <div class="login__content">
        <div class="login__inner">

            <h1 class="login__title">
                ログイン
            </h1>

            <form action="{{ route('login') }}" method="POST" class="login__form" novalidate>
                @csrf
                <input type="hidden" name="login_type" value="user">

                @php
                    $hasAuthError = $errors->has('email') && in_array('ログイン情報が登録されていません', $errors->get('email'), true);
                @endphp

                <div class="login__form-group">
                    <div class="login__field">
                        <label class="login__label">メールアドレス</label>
                        <input type="email" name="email" class="login__input u-input {{ $errors->has('email') ? 'is-error' : '' }}" value="{{ old('email') }}">
                    </div>
                    <div class="login__errors">
                        @if ($errors->has('email') && ! $hasAuthError)
                            <ul class="login__error">
                                @foreach ($errors->get('email') as $error)
                                    <li class="error-list">
                                        {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="login__form-group">
                    <div class="login__field">
                        <label class="login__label">パスワード</label>
                        <input type="password" name="password" class="login__input u-input {{ ($errors->has('password') || $hasAuthError) ? 'is-error' : '' }}">
                    </div>

                    <div class="login__errors">
                        @if ($errors->has('password'))
                            <ul class="login__error">
                                @foreach ($errors->get('password') as $error)
                                    <li class="error-list">
                                    {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    @if ($hasAuthError)
                        <ul class="login__error">
                            @foreach ($errors->get('email') as $error)
                                @if (str_contains($error, 'ログイン情報'))
                                    <li class="error-list">
                                        {{ $error }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>

                <button class="login__submit" type="submit">
                    ログインする
                </button>

            </form>

            <div class="login__link">
                <a href="{{ route('register') }}" class="login__link-text">
                    会員登録はこちら
                </a>
            </div>

        </div>
    </div>
@endsection