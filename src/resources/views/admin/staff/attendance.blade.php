@extends('layouts.app')

@section('head-title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance__content">
        <div class="attendance__inner">
            <h1 class="attendance__title">
                {{$user->name}}さんの勤怠
            </h1>

            <div class="attendance__menu">
                <a href="{{ route('admin.staff.attendance.list', ['id' => $user->id ,'month' => $previousMonth]) }}" class="attendance__link">← 前月</a>

                <div class="attendance__month">
                    {{$month}}
                </div>

                <a href="{{ route('admin.staff.attendance.list', ['id' => $user->id ,'month' => $nextMonth]) }}" class="attendance__link">翌月 →</a>
            </div>

            <table class="attendance__table">
                <tr class="attendance__row attendance__row--header">
                    <th class="attendance__header attendance__header--date">日付</th>
                    <th class="attendance__header">出勤</th>
                    <th class="attendance__header">退勤</th>
                    <th class="attendance__header">休憩</th>
                    <th class="attendance__header">合計</th>
                    <th class="attendance__header">詳細</th>
                    <th class="attendance__header">申請</th>
                </tr>

                @foreach ($attendanceList as $attendance)
                    <tr class="attendance__row">
                        <td class="attendance__data attendance__data--date">{{ $attendance['date_label'] }}</td>
                        <td class="attendance__data">{{ $attendance['clock_in_at'] }}</td>
                        <td class="attendance__data">{{ $attendance['clock_out_at'] }}</td>
                        <td class="attendance__data">{{ $attendance['break_time'] }}</td>
                        <td class="attendance__data">{{ $attendance['work_time'] }}</td>
                        <td class="attendance__data">
                            @if ($attendance['is_future'])
                                <a href="#" class="attendance__table-link is-disabled">
                                    詳細
                                </a>
                            @elseif ($attendance['attendance_id'])
                                <a href="{{ route('admin.attendance.detail', ['id' => $attendance['attendance_id']]) }}" class="attendance__table-link">
                                    詳細
                                </a>
                            @else
                                <a href="{{ route('admin.attendance.create', ['user_id' => $user->id, 'work_date' => $attendance['work_date']]) }}" class="attendance__table-link">
                                    詳細
                                </a>
                            @endif
                        </td>
                        <td class="attendance__data {{ $attendance['request_status'] === '申請中' ? 'is-pending' : ($attendance['request_status'] === '承認済み' ? 'is-approved' : '')}}">
                            {{ $attendance['request_status'] }}
                        </td>
                    </tr>
                @endforeach
            </table>

            <div class="attendance__action">
                <a href="{{ route('admin.attendance.staff.csv', ['id' => $user->id, 'month' => request('month', now()->format('Y-m'))]) }}" class="attendance__button">
                    CSV出力
                </a>
            </div>
        </div>
    </div>
@endsection