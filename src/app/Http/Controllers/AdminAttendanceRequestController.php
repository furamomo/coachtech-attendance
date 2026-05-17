<?php

namespace App\Http\Controllers;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminAttendanceRequestController extends Controller
{
    public function show($attendance_correct_request_id)
    {
        $attendanceRequest = AttendanceRequest::with([
            'user',
            'attendance',
            'requestBreaks',
        ])->findOrFail($attendance_correct_request_id);

        $detail = [
            'name' => $attendanceRequest->user->name,
            'year' => Carbon::parse($attendanceRequest->attendance->work_date)->format('Y年'),
            'date' => Carbon::parse($attendanceRequest->attendance->work_date)->format('n月j日'),
            'clock_in_at' => $attendanceRequest->clock_in_at
                ? Carbon::parse($attendanceRequest->clock_in_at)->format('H:i')
                : '',
            'clock_out_at' => $attendanceRequest->clock_out_at
                ? Carbon::parse($attendanceRequest->clock_out_at)->format('H:i')
                : '',
            'note' => $attendanceRequest->note ?? '',
            'breaks' => [],
        ];

        foreach ($attendanceRequest->requestBreaks as $break) {
            $detail['breaks'][] = [
                'break_start_at' => $break->break_start_at
                    ? Carbon::parse($break->break_start_at)->format('H:i')
                    : '',
                'break_end_at' => $break->break_end_at
                    ? Carbon::parse($break->break_end_at)->format('H:i')
                    : '',
            ];
        }

        return view('admin.request.detail', compact('attendanceRequest', 'detail'));
    }

    public function approve($attendance_correct_request_id)
    {
        $attendanceRequest = AttendanceRequest::with(['attendance', 'requestBreaks'])
            ->findOrFail($attendance_correct_request_id);

        $attendanceRequest->update([
            'status' => AttendanceRequest::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        $attendance = $attendanceRequest->attendance;

        $attendance->update([
            'clock_in_at' => $attendanceRequest->clock_in_at,
            'clock_out_at' => $attendanceRequest->clock_out_at,
            'note' => $attendanceRequest->note,
        ]);

        AttendanceBreak::where('attendance_id', $attendance->id)->delete();

        foreach ($attendanceRequest->requestBreaks as $break) {
            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start_at' => $break->break_start_at,
                'break_end_at' => $break->break_end_at,
            ]);
        }

        return redirect()->route('stamp.request.approve', [
            'attendance_correct_request_id' => $attendanceRequest->id,
        ]);
    }
}
