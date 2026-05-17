<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceRequestController extends Controller
{
    public function store(AttendanceCorrectionRequest $request, $id): RedirectResponse
    {
        $date = $id;
        $dateCarbon = Carbon::parse($date);

        $attendance = Attendance::with(['breaks', 'attendanceRequests'])
            ->where('user_id', Auth::id())
            ->whereDate('work_date', $date)
            ->first();

        if ($dateCarbon->isFuture()) {
            return redirect()
                ->route('attendance.detail', ['id' => $date])
                ->with('error', '未来日の勤怠は修正できません。');
        }

        if ($dateCarbon->isToday() && (!$attendance || !$attendance->clock_out_at)) {
            return redirect()
                ->route('attendance.detail', ['id' => $date])
                ->with('error', '退勤していないため修正はできません。');
        }

        if (!$attendance) {
            $attendance = Attendance::create([
                'user_id' => Auth::id(),
                'work_date' => $date,
                'clock_in_at' => null,
                'clock_out_at' => null,
                'note' => null,
            ]);
        }

        $isPending = $attendance->attendanceRequests()
            ->where('status', AttendanceRequest::STATUS_PENDING)
            ->exists();

        if ($isPending) {
            return redirect()
                ->route('attendance.detail', ['id' => $date])
                ->with('error', '承認待ちのため修正はできません。');
        }

        DB::transaction(function() use ($request, $attendance, $date) {
            $attendanceRequest = AttendanceRequest::create([
                'user_id' => Auth::id(),
                'attendance_id' => $attendance->id,
                'clock_in_at' => $request->input('clock_in_at')
                    ? Carbon::parse($date . ' ' . $request->input('clock_in_at'))
                    : null,
                'clock_out_at' => $request->input('clock_out_at')
                    ? Carbon::parse($date . ' ' . $request->input('clock_out_at'))
                    : null,
                'note' => $request->input('note'),
                'status' => AttendanceRequest::STATUS_PENDING,
            ]);

            $breaks = $request->input('breaks', []);

            foreach ($breaks as $break) {
                $start = $break['start'] ?? null;
                $end = $break['end'] ?? null;

                if (blank($start) && blank($end)) {
                    continue;
                }

                $attendanceRequest->requestBreaks()->create([
                    'break_start_at' => $start
                        ? Carbon::parse($date . ' ' . $start)
                        : null,
                    'break_end_at' => $end
                        ? Carbon::parse($date . ' ' . $end)
                        : null,
                ]);
            }
        });

        return redirect()
            ->route('attendance.detail', ['id' => $date])
            ->with('success', '修正申請を送信しました。');
    }

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'pending');

        $query = AttendanceRequest::with(['user', 'attendance'])
            ->where('user_id', auth()->id());

        if ($tab === 'approved') {
            $query->where('status', AttendanceRequest::STATUS_APPROVED);
        } else {
            $query->where('status', AttendanceRequest::STATUS_PENDING);
        }

        $requests = $query->latest()->get();

        return view('attendance.request.list', compact('requestList'));
    }

    public function show($id)
    {
        $attendanceRequest = AttendanceRequest::with(['user', 'attendance', 'requestBreaks'])
            ->where('id', $id)
            ->firstOrFail();

        $detail = [
            'name' => $attendanceRequest->user->name,
            'year' => Carbon::parse($attendanceRequest->attendance->work_date)->format('Y年'),
            'date' => Carbon::parse($attendanceRequest->attendance->work_date)->format('n月j日'),
            'clock_in_at' => $attendanceRequest->clock_in_at ? Carbon::parse($attendanceRequest->clock_in_at)->format('H:i') : '',
            'clock_out_at' => $attendanceRequest->clock_out_at ? Carbon::parse($attendanceRequest->clock_out_at)->format('H:i') : '',
            'note' => $attendanceRequest->note ?? '',
            'breaks' => $attendanceRequest->requestBreaks->map(function ($break) {
                return [
                    'break_start_at' => $break->break_start_at
                        ? Carbon::parse($break->break_start_at)->format('H:i')
                        : '',
                    'break_end_at' => $break->break_end_at
                        ? Carbon::parse($break->break_end_at)->format('H:i')
                        : '',
                ];
            })->toArray(),
        ];

        $isPending = !$attendanceRequest->isApproved();

        return view('attendance.request.detail', compact('attendanceRequest', 'detail', 'isPending'));
    }
}
