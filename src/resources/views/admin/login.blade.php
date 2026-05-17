@extends('layouts.app')

@section('head-title', '管理者ログイン画面')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/login.css') }}">
@endsection

@section('content')
    <div class="login__content">
        <div class="login__inner">

            <h1 class="login__title">
                管理者ログイン
            </h1>

            <form action="{{ route('login') }}" method="POST" class="login__form" novalidate>

                @csrf
                <input type="hidden" name="login_type" value="admin">

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
                        <input type="password" name="password" class="login__input u-input {{ $errors->has('password') || $hasAuthError ? 'is-error' : '' }}">
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

                        @if ($hasAuthError)
                            <ul class="login__error">
                                @foreach($errors->get('email') as $error)
                                    <li class="error-list">
                                        {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <button class="login__submit" type="submit">
                    管理者ログインする
                </button>

            </form>
        </div>
    </div>
@endsection