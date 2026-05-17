@extends('layouts.app')

@section('head-title', 'スッタフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/list.css') }}">
@endsection

@section('content')
    <div class="list__content">
        <div class="list__inner">
            <h1 class="list__title">
                スタッフ一覧
            </h1>

            <table class="list__table">
                <thead>
                    <tr class="list__row list__row--header">
                        <th class="list__header list__header--date">名前</th>
                        <th class="list__header">メールアドレス</th>
                        <th class="list__header">月次勤怠</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($staffs as $staff)
                        <tr class="list__row">
                            <td class="list__data list__data--reason">{{ $staff->name }}</td>
                            <td class="list__data list__data--reason">{{ $staff->email }}</td>
                            <td class="list__data">
                                <a href="{{route('admin.staff.attendance.list', ['id' => $staff->id])}}" class="list__table-link">
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