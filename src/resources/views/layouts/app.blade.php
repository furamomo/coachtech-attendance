<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>@yield('head-title', 'COACHTECH')</title>

        <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
        <link rel="stylesheet" href="{{ asset('css/common.css') }}">
        @yield('css')
    </head>

    <body class="{{ auth()->check() ? 'is-auth__body' : '' }}">
        <header class="header">
            <div class="header__inner">

                {{-- ロゴ --}}
                <div class="header__logo">
                    <a href="#" class="header__logo-link">
                        <img src="{{ asset('images/coachtech-header.png') }}" alt="COACHTECH" class="header__logo-image">
                    </a>
                </div>

                {{-- ナビ --}}
                @if (empty($hideHeaderNav) || ! $hideHeaderNav)
                    <nav class="header__nav">

                        {{-- 未ログイン --}}
                        @guest
                            {{-- 未表示--}}
                        @endguest


                        {{-- ログイン中 --}}
                        @auth

                            {{-- 管理者 --}}
                            @if(auth()->user()->is_admin)

                                <a href="{{route('admin.attendance.list')}}" class="header__link">
                                    勤怠一覧
                                </a>

                                <a href="{{route('admin.staff.list')}}" class="header__link">
                                    スタッフ一覧
                                </a>

                                <a href="{{ route('stamp.request.list') }}" class="header__link">
                                    申請一覧
                                </a>


                            {{-- 一般ユーザー --}}
                            @else

                                {{-- 退勤後画面 --}}
                                @if(($status ?? null) === 'finished' )

                                    <a href="{{ route('attendance.list') }}" class="header__link">
                                        今月の出勤一覧
                                    </a>

                                    <a href="{{ route('stamp.request.list') }}" class="header__link">
                                        申請一覧
                                    </a>

                                {{-- 通常勤怠画面 --}}
                                @else

                                    <a href="{{ route('attendance.index') }}" class="header__link">
                                        勤怠
                                    </a>

                                    <a href="{{ route('attendance.list') }}" class="header__link">
                                        勤怠一覧
                                    </a>

                                    <a href="{{ route('stamp.request.list') }}" class="header__link">
                                        申請
                                    </a>

                                @endif

                            @endif

                            {{-- ログアウト --}}
                            <form action="{{ route('logout')}}" method="POST" class="header__link-form">
                                @csrf
                                <input type="hidden" name="logout_type" value="{{ auth()->user()->is_admin ? 'admin' : 'user' }}">
                                <button type="submit" class="header__link">
                                ログアウト
                                </button>
                            </form>
                        @endauth

                    </nav>
                @endif
            </div>
        </header>

        <main class="main">
            @yield('content')
        </main>
    </body>
</html>