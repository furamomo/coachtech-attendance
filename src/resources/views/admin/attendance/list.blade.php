@extends('layouts.app')

@section('head-title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')
    <div class="list__content">
        <div class="list__inner">
            <h1 class="list__title">
                {{ $date . 'の勤怠' }}
            </h1>

            <div class="list__menu">
                <a href="{{ route('admin.attendance.list', ['day' => $previousDay]) }}" class="list__link">
                    ← 前日
                </a>

                <div class="list__date">
                    {{ $displayDay }}
                </div>

                <a href="{{ route('admin.attendance.list', ['day' => $nextDay]) }}" class="list__link">
                    翌日 →
                </a>
            </div>

            <table class="list__table">
                <thead>
                    <tr class="list__row list__row--header">
                        <th class="list__header list__header--date">名前</th>
                        <th class="list__header">出勤</th>
                        <th class="list__header">退勤</th>
                        <th class="list__header">休憩</th>
                        <th class="list__header">合計</th>
                        <th class="list__header">詳細</th>
                        <th class="list__header">申請</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($dayAttendanceList as $dayAttendance)
                        <tr class="list__row">
                            <td class="list__data list__data--reason">{{ $dayAttendance->name }}</td>
                            <td class="list__data">{{ $dayAttendance->clock_in_at }}</td>
                            <td class="list__data">{{ $dayAttendance->clock_out_at }}</td>
                            <td class="list__data">{{ $dayAttendance->break_time }}</td>
                            <td class="list__data">{{ $dayAttendance->work_time }}</td>
                            <td class="list__data">
                                @if ($currentDay->isFuture())
                                    <span class="list__table-link list__table-link--disabled">
                                        詳細
                                    </span>
                                @elseif ($dayAttendance->id)
                                    <a href="{{ route('admin.attendance.detail', ['id' => $dayAttendance->id]) }}" class="list__table-link">
                                        詳細
                                    </a>
                                @else
                                    <a href="{{ route('admin.attendance.create', ['user_id' => $dayAttendance->user_id, 'work_date' => $dayAttendance->work_date]) }}" class="list__table-link">
                                        詳細
                                    </a>
                                @endif
                            </td>
                            <td class="list__data {{ $dayAttendance->request_status === '申請中' ? 'is-pending' : ($dayAttendance->request_status === '承認済み' ? 'is-approved' : '') }}">
                                {{ $dayAttendance->request_status }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection