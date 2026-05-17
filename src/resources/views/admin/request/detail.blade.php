@extends('layouts.app')

@section('head-title', '申請承認')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request/detail.css') }}">
@endsection

@section('content')
    <div class="detail__content">
        <div class="detail__inner">
            <h1 class="detail__title">
                勤怠詳細
            </h1>

            <form action="{{ route('stamp.request.approve.update', ['attendance_correct_request_id' => $attendanceRequest->id]) }}" method="POST" class="detail__form">
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
                                <div class="detail__input detail__input--data">
                                    {{ $detail['clock_in_at'] }}
                                </div>
                                <p class="detail__form-item">～</p>
                                <div class="detail__input detail__input--data">
                                    {{ $detail['clock_out_at'] }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @foreach ($detail['breaks'] as $index => $break)
                        <div class="detail__form-row">
                            <div class="detail__form-area">
                                <div class="detail__form-label">
                                    {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                                </div>

                                <div class="detail__form-value">
                                    <div class="detail__input detail__input--data">
                                        {{ $break['break_start_at'] }}
                                    </div>
                                    <p class="detail__form-item">～</p>
                                    <div class="detail__input detail__input--data">
                                        {{ $break['break_end_at'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="detail__form-row detail__form-row--textarea">
                        <div class="detail__form-area">
                            <div class="detail__form-label">
                                備考
                            </div>

                            <div class="detail__form-value">
                                <div class="detail__input detail__input--textarea-data">
                                    {{ $detail['note'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail__form-button">
                    @if ($attendanceRequest->isApproved())
                        <button type="button" class="detail__form-submit detail__form-submit--done" disabled>
                            承認済み
                        </button>
                    @else
                        <button type="submit" class="detail__form-submit">
                            承認
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection