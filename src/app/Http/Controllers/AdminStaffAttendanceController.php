<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;


class AdminStaffAttendanceController extends Controller
{
    public function index(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $monthParam = $request->query('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m-d', $monthParam . '-01');

        $month = $currentMonth->format('Y/m');

        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breaks', 'attendanceRequests')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->orderBy('work_date')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->toDateString();
            });

        $attendanceList = [];
        $date = $startOfMonth->copy();

        while ($date->lte($endOfMonth)) {
            $dateString = $date->toDateString();
            $attendance = $attendances->get($dateString);

            $clockInAt = '';
            $clockOutAt = '';
            $breakTime = '';
            $workTime = '';
            $requestStatus = '';

            if ($attendance) {
                if ($attendance->clock_in_at) {
                    $clockInAt = Carbon::parse($attendance->clock_in_at)->format('H:i');
                }

                if ($attendance->clock_out_at) {
                    $clockOutAt = Carbon::parse($attendance->clock_out_at)->format('H:i');
                }

                $totalBreakMinutes = 0;

                foreach ($attendance->breaks as $break) {
                    if ($break->break_start_at && $break->break_end_at) {
                        $breakStart = Carbon::parse($break->break_start_at)->startOfMinute();
                        $breakEnd = Carbon::parse($break->break_end_at)->startOfMinute();

                        $totalBreakMinutes += $breakEnd->diffInMinutes($breakStart);
                    }
                }

                if ($attendance->clock_out_at) {
                    $breakHours = floor($totalBreakMinutes / 60);
                    $breakMinutes = $totalBreakMinutes % 60;
                    $breakTime = sprintf('%d:%02d', $breakHours, $breakMinutes);
                }

                if ($attendance->clock_in_at && $attendance->clock_out_at) {
                    $workStart = Carbon::parse($attendance->clock_in_at)->startOfMinute();
                    $workEnd = Carbon::parse($attendance->clock_out_at)->startOfMinute();

                    $totalWorkMinutes = $workEnd->diffInMinutes($workStart) - $totalBreakMinutes;

                    $workHours = floor($totalWorkMinutes / 60);
                    $workMinutes = $totalWorkMinutes % 60;
                    $workTime = sprintf('%d:%02d', $workHours, $workMinutes);
                }

                $latestRequest = $attendance->attendanceRequests
                    ->sortByDesc('created_at')
                    ->first();

                if ($latestRequest) {
                    $requestStatus = match ($latestRequest->status) {
                        0 => '申請中',
                        1 => '承認済み',
                        default => '',
                    };
                }
            }



            $attendanceList[] = [
                'work_date' => $date->toDateString(),
                'date_label' => $date->format('m/d') . '（' . ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] . '）',
                'clock_in_at' => $clockInAt,
                'clock_out_at' => $clockOutAt,
                'break_time' => $breakTime,
                'work_time' => $workTime,
                'attendance_id' => $attendance ? $attendance->id : null,
                'is_future' => $date->isFuture(),
                'request_status' => $requestStatus,
            ];

            $date->addDay();
        }

        return view('admin.staff.attendance', compact('user', 'month', 'previousMonth', 'nextMonth', 'attendanceList'
        ));
    }
}