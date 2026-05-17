@extends('layouts.app')

@section('head-title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
    <div class="detail__content">
        <div class="detail__inner">
            <h1 class="detail__title">
                勤怠詳細
            </h1>

            <form action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}" method="POST" class="detail__form">
                @csrf

                <div class="detail__form-group">
                    <div class="detail__form-row detail__form-row--data">
                        <div class="detail__form-label">
                            名前
                        </div>
                        <div class="detail__form-text">
                            {{ $detail['name'] }}
                        </div>
                    </div>

                    <div class="detail__form-row detail__form-row--data">
                        <div class="detail__form-label">
                            日付
                        </div>
                        <div class="detail__form-text">
                            <div class="detail__form-data">
                                {{ $detail['year'] }}
                            </div>
                            <div class="detail__form-data">
                                {{ $detail['date'] }}
                            </div>
                        </div>
                    </div>

                    <div class="detail__form-row">
                        <div class="detail__form-area">
                            <div class="detail__form-label">
                                出勤・退勤
                            </div>

                            <div class="detail__form-value">
                                @if ($isDisabled)
                                    <div class="detail__input detail__input--data">{{ $detail['clock_in_at'] }}</div>
                                    <p class="detail__form-item">～</p>
                                    <div class="detail__input detail__input--data">{{ $detail['clock_out_at'] }}</div>
                                @else
                                    <input type="text" name="clock_in_at" class="detail__input" value="{{ old('clock_in_at', $detail['clock_in_at']) }}">
                                    <p class="detail__form-item">～</p>
                                    <input type="text" name="clock_out_at" class="detail__input" value="{{ old('clock_out_at', $detail['clock_out_at']) }}">
                                @endif
                            </div>
                        </div>

                        @if ($errors->has('clock_in_at'))
                            <ul class="detail__error">
                                @foreach ($errors->get('clock_in_at') as $error)
                                    <li class="error-list">{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($errors->has('clock_out_at'))
                            <ul class="detail__error">
                                @foreach ($errors->get('clock_out_at') as $error)
                                    <li class="error-list">{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    @foreach ($detail['breaks'] as $index => $break)
                        <div class="detail__form-row">
                            <div class="detail__form-area">
                                <div class="detail__form-label">
                                    {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                                </div>

                                <div class="detail__form-value">
                                    @if ($isDisabled)
                                        <div class="detail__input detail__input--data">
                                            {{ $break['break_start_at'] }}
                                        </div>
                                        <p class="detail__form-item">～</p>
                                        <div class="detail__input detail__input--data">
                                            {{ $break['break_end_at'] }}
                                        </div>
                                    @else
                                        <input type="text" name="breaks[{{ $index }}][start]" class="detail__input" value="{{ old("breaks.$index.start", $break['break_start_at']) }}">
                                        <p class="detail__form-item">～</p>
                                        <input type="text" name="breaks[{{ $index }}][end]" class="detail__input" value="{{ old("breaks.$index.end", $break['break_end_at']) }}">
                                    @endif
                                </div>
                            </div>

                            @if ($errors->has("breaks.$index.start"))
                                <ul class="detail__error">
                                    @foreach (array_unique($errors->get("breaks.$index.start")) as $error)
                                        <li class="error-list">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach

                    @if (!$isDisabled)
                        @php
                            $newBreakIndex = count($detail['breaks']);
                        @endphp

                        <div class="detail__form-row">
                            <div class="detail__form-area">
                                <div class="detail__form-label">
                                    {{ $newBreakIndex === 0 ? '休憩' : '休憩' . ($newBreakIndex + 1) }}
                                </div>

                                <div class="detail__form-value">
                                    <input type="text" name="breaks[{{ $newBreakIndex }}][start]" class="detail__input" value="{{ old("breaks.$newBreakIndex.start") }}">
                                    <p class="detail__form-item">～</p>
                                    <input type="text" name="breaks[{{ $newBreakIndex }}][end]" class="detail__input" value="{{ old("breaks.$newBreakIndex.end") }}">
                                </div>
                            </div>

                            @if ($errors->has("breaks.$newBreakIndex.start"))
                                <ul class="detail__error">
                                    @foreach (array_unique($errors->get("breaks.$newBreakIndex.start")) as $error)
                                        <li class="error-list">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif

                    <div class="detail__form-row detail__form-row--textarea">
                        <div class="detail__form-area">
                            <div class="detail__form-label">
                                備考
                            </div>

                            <div class="detail__form-value">
                                @if ($isDisabled)
                                    <div class="detail__input detail__input--textarea-data">{{ $detail['note'] }}</div>
                                @else
                                    <textarea name="note" class="detail__input detail__input--textarea">{{ old('note', $detail['note']) }}</textarea>
                                @endif
                            </div>
                        </div>

                        @if ($errors->has('note'))
                            <ul class="detail__error">
                                @foreach ($errors->get('note') as $error)
                                    <li class="error-list">{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="detail__form-button">
                    @if ($isPending)
                        <p class="detail__message">
                            *承認待ちのため修正はできません。
                        </p>
                    @elseif ($isWorking)
                        <p class="detail__message">
                            *退勤していないため修正はできません。
                        </p>
                    @else
                        <button type="submit" class="detail__form-submit">
                            修正
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection