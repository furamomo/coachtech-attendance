@extends('layouts.app', ['hideHeaderSearch' => true, 'hideHeaderNav' => true])

@section('head-title', 'Verify Email')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection


@section('content')
    <div class="verify-email__content">
        <div class="verify-email__inner">
            <p class="verify-email__message">
                登録していただいたメールアドレスに認証メールを送付しました。<br>
                メール認証を完了してください。
            </p>

            <a class="verify-email__button" href="{{ config('services.mailhog.url') }}" rel="noopener">
                認証はこちらから
            </a>

            <div class="verify-email__resend">
                <form action="{{ route('verification.send') }}" method="POST" class="verify-email__form">
                    @csrf
                    <button type="submit" class="verify-email__resend-link">
                        認証メールを再送する
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
