@extends('layouts.app')

@section('head-title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/create.css') }}">
@endsection

@section('content')
    <div class="create__content">
        <div class="create__inner">
            <h1 class="create__title">
                勤怠詳細
            </h1>

            <form action="{{ route('admin.attendance.store', ['user_id' => $user->id, 'work_date' => $detail['work_date']]) }}" method="POST" class="create__form">
                @csrf


                <div class="create__form-group">
                    <div class="create__form-row create__form-row--data">
                        <div class="create__form-label">
                            名前
                        </div>
                        <div class="create__form-text">
                            {{ $detail['name'] }}
                        </div>
                    </div>

                    <div class="create__form-row create__form-row--data">
                        <div class="create__form-label">
                            日付
                        </div>
                        <div class="create__form-text">
                            <div class="create__form-data">
                                {{ $detail['year'] }}
                            </div>
                            <div class="create__form-data">
                                {{ $detail['date'] }}
                            </div>
                        </div>
                    </div>

                    <div class="create__form-row">
                        <div class="create__form-area">
                            <div class="create__form-label">
                                出勤・退勤
                            </div>

                            <div class="create__form-value">
                                <input type="text" name="clock_in_at" class="create__input" value="{{ old('clock_in_at', $detail['clock_in_at']) }}">
                                <p class="create__form-item">～</p>
                                <input type="text" name="clock_out_at" class="create__input" value="{{ old('clock_out_at', $detail['clock_out_at']) }}">
                            </div>
                        </div>

                        @if ($errors->has('clock_in_at'))
                            <ul class="create__error">
                                @foreach ($errors->get('clock_in_at') as $error)
                                    <li class="error-list">{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($errors->has('clock_out_at'))
                            <ul class="create__error">
                                @foreach ($errors->get('clock_out_at') as $error)
                                    <li class="error-list">{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    @foreach ($detail['breaks'] as $index => $break)
                        <div class="create__form-row">
                            <div class="create__form-area">
                                <div class="create__form-label">
                                    休憩
                                </div>

                                <div class="create__form-value">
                                    <input type="text" name="breaks[0][start]" class="create__input" value="{{ old('breaks.0.start') }}">
                                    <p class="create__form-item">～</p>
                                    <input type="text" name="breaks[0][end]" class="create__input" value="{{ old('breaks.0.end') }}">
                                </div>

                            </div>

                            @if ($errors->has("breaks.$index.start"))
                                <ul class="create__error">
                                    @foreach (array_unique($errors->get("breaks.$index.start")) as $error)
                                        <li class="error-list">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach

                    <div class="create__form-row create__form-row--textarea">
                        <div class="create__form-area">
                            <div class="create__form-label">
                                備考
                            </div>

                            <div class="create__form-value">
                                <textarea name="note" class="create__input create__input--textarea">{{ old('note', $detail['note']) }}</textarea>
                            </div>
                        </div>

                        @if ($errors->has('note'))
                            <ul class="create__error">
                                @foreach ($errors->get('note') as $error)
                                    <li class="error-list">{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="create__form-button">
                    @if ($date->isFuture())
                        <p class="create__message">
                            *未来日の勤怠は修正できません。
                        </p>
                    @elseif($isToday)
                        <p class="create__message">
                            *当日の未打刻勤怠は修正できません。
                        </p>
                    @else
                        <button type="submit" class="create__form-submit">
                            修正
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection