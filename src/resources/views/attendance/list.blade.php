@extends('layouts.app')

@section('head-title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@section('content')
    <div class="list__content">
        <div class="list__inner">
            <h1 class="list__title">
                勤怠一覧
            </h1>

            <div class="list__menu">
                <a href="{{ route('attendance.list', ['month' => $previousMonth]) }}" class="list__link">← 前月</a>

                <div class="list__month">
                    {{$month}}
                </div>

                <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="list__link">翌月 →</a>
            </div>

            <table class="list__table">
                <tr class="list__row link__row--header">
                    <th class="list__header list__header--date">日付</th>
                    <th class="list__header">出勤</th>
                    <th class="list__header">退勤</th>
                    <th class="list__header">休憩</th>
                    <th class="list__header">合計</th>
                    <th class="list__header">詳細</th>
                    <th class="list__header">申請</th>
                </tr>

                @foreach ($attendanceList as $attendance)
                    <tr class="list__row">
                        <td class="list__data list__data--date">{{ $attendance['date_label'] }}</td>
                        <td class="list__data">{{ $attendance['clock_in_at'] }}</td>
                        <td class="list__data">{{ $attendance['clock_out_at'] }}</td>
                        <td class="list__data">{{ $attendance['break_time'] }}</td>
                        <td class="list__data">{{ $attendance['work_time'] }}</td>
                        <td class="list__data">
                            <a href="{{ !$attendance['is_future'] ? route('attendance.detail',['id' => $attendance['work_date']]) : '#' }}" class="list__table-link {{ $attendance['is_future'] ? 'is-disabled' : '' }}">
                                詳細
                            </a>
                        </td>
                        <td class="list__data {{ $attendance['request_status'] === '申請中' ? 'is-pending' : ($attendance['request_status'] === '承認済み' ? 'is-approved' : '')}}">
                            {{ $attendance['request_status'] }}
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection