@extends('layouts.app')

@section('head-title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request/list.css') }}">
@endsection

@section('content')
    <div class="list__content">
        <div class="list__inner">
            <h1 class="list__title">
                申請一覧
            </h1>

            <div class="list__menu">
                <a href="{{ route('stamp.request.list', ['tab' => 'pending']) }}" class="list__tab {{ request('tab', 'pending') === 'pending' ? 'is-active' : '' }}">
                    承認待ち
                </a>

                <a href="{{ route('stamp.request.list', ['tab' => 'approved']) }}" class="list__tab {{ request('tab') === 'approved' ? 'is-active' : '' }}">
                    承認済み
                </a>
            </div>

            <table class="list__table">
                <thead>
                    <tr class="list__row list__row--header">
                        <th class="list__header list__space"></th>
                        <th class="list__header">状態</th>
                        <th class="list__header">名前</th>
                        <th class="list__header">対象日時</th>
                        <th class="list__header">申請理由</th>
                        <th class="list__header">申請日時</th>
                        <th class="list__header list__narrow">詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($requests as $request)
                        <tr class="list__row">
                            <td class="list__data list__space"></td>
                            <td class="list__data">
                                {{ $request->approved_at ? '承認済み' : '承認待ち' }}
                            </td>

                            <td class="list__data list__data--reason">
                                {{ $request->user->name }}
                            </td>

                            <td class="list__data">
                                {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}
                            </td>

                            <td class="list__data list__data--reason">
                                {{ $request->note }}
                            </td>

                            <td class="list__data">
                                {{ $request->created_at->format('Y/m/d') }}
                            </td>

                            <td class="list__data list__narrow">
                                <a href="{{ route('stamp.request.approve', ['attendance_correct_request_id' => $request->id]) }}" class="list__link">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection