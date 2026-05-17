@extends('layouts.app')

@section('head-title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
    <div class="register__content">
        <div class="register__inner">

            <h1 class="register__title">
                会員登録
            </h1>

            <form action="{{ route('register') }}" method="POST" class="register__form" novalidate>

                @csrf
                <div class="register__form-group">
                    <div class="register__field">
                        <label class="register__label">名前</label>
                        <input type="text" name="name" class="register__input u-input {{ $errors->has('name') ? 'is-error' : '' }}" value="{{ old('name') }}">
                    </div>
                    <div class="register__errors">
                        @if ($errors->has('name'))
                            <ul class="register__error">
                                @foreach ($errors->get('name') as $error)
                                    <li class="error-list">
                                    {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="register__form-group">
                    <div class="register__field">
                        <label class="register__label">メールアドレス</label>
                        <input type="email" name="email" class="register__input u-input {{ $errors->has('email') ? 'is-error' : '' }}" value="{{ old('email') }}">
                    </div>
                    <div class="register__errors">
                        @if ($errors->has('email'))
                            <ul class="register__error">
                                @foreach ($errors->get('email') as $error)
                                    <li class="error-list">
                                    {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="register__form-group">
                    <div class="register__field">
                        <label class="register__label">パスワード</label>
                        <input type="password" name="password" class="register__input u-input {{ $errors->has('password') || $errors->has('password_confirmation') ? 'is-error' : '' }}">
                    </div>
                </div>

                <div class="register__form-group">
                    <div class="register__field">
                        <label class="register__label">パスワード確認</label>
                        <input type="password" name="password_confirmation" class="register__input u-input {{ $errors->has('password') || $errors->has('password_confirmation') ? 'is-error' : '' }}">
                    </div>

                    <div class="register__errors">
                        @if ($errors->has('password') || $errors->has('password_confirmation'))
                            <ul class="register__error">
                                @foreach ($errors->get('password') as $error)
                                    <li class="error-list">
                                    {{ $error }}
                                    </li>
                                @endforeach

                                @foreach($errors->get('password_confirmation') as $error)
                                    <li class="register__error-list">
                                        {{$error}}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <button class="register__submit" type="submit">
                    登録する
                </button>

            </form>

            <div class="register__link">
                <a href="{{ route('login') }}" class="register__link-text">
                    ログインはこちら
                </a>
            </div>

        </div>
    </div>
@endsection