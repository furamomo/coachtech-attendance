@extends('layouts.app')

@section('head-title', '勤怠')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
    <div class="attendance__content">
        <div class="attendance__inner">

            <div class="attendance__status">
                @if ($status === 'working_out')
                    <span class="attendance__status-badge">勤務外</span>
                @elseif ($status === 'working')
                    <span class="attendance__status-badge">出勤中</span>
                @elseif ($status === 'on_break')
                    <span class="attendance__status-badge">休憩中</span>
                @elseif ($status === 'finished')
                    <span class="attendance__status-badge">退勤済</span>
                @endif
            </div>

            <p class="attendance__date">{{ $date }}</p>
            <p class="attendance__time" id="currentTime">{{ $time }}</p>

            <div class="attendance__actions">
                @if ($status === 'working_out')
                    <form action="{{ route('attendance.clockIn') }}" method="POST" class="attendance__form">
                        @csrf
                        <button type="submit" class="attendance__button attendance__button--primary">
                            出勤
                        </button>
                    </form>

                @elseif ($status === 'working')
                    <form action="{{ route('attendance.clockOut') }}" method="POST" class="attendance__form">
                        @csrf
                        <button type="submit" class="attendance__button attendance__button--primary">
                            退勤
                        </button>
                    </form>

                    <form action="{{ route('attendance.breakIn') }}" method="POST" class="attendance__form">
                        @csrf
                        <button type="submit" class="attendance__button attendance__button--secondary">
                            休憩入
                        </button>
                    </form>

                @elseif ($status === 'on_break')
                    <form action="{{ route('attendance.breakOut') }}" method="POST" class="attendance__form">
                        @csrf
                        <button type="submit" class="attendance__button attendance__button--secondary">
                            休憩戻
                        </button>
                    </form>

                @elseif ($status === 'finished')
                    <p class="attendance__message">お疲れ様でした。</p>
                @endif
            </div>

        </div>
    </div>

    <script src="{{ asset('js/attendance.js') }}"></script>
@endsection