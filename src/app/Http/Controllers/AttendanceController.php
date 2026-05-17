<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        Carbon::setLocale('ja');

        $now = Carbon::now();

        $date = $now->isoFormat('YYYY年M月D日(ddd)');
        $time = $now->format('H:i');

        $attendance = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->first();

        if (! $attendance) {
            $status = 'working_out';
        } elseif ($attendance->clock_out_at) {
            $status = 'finished';
        } elseif ($attendance->breaks()->whereNull('break_end_at')->exists()) {
            $status = 'on_break';
        } else {
            $status = 'working';
        }

        return view('attendance.index', compact('date', 'time', 'status', 'attendance'));
    }

    public function clockIn()
    {
        $attendance = $this->getTodayAttendance();

        if($attendance) {
            return redirect()->route('attendance.index');
        }

        Attendance::create([
            'user_id' => Auth::id(),
            'work_date' => today(),
            'clock_in_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function clockOut()
    {
        $attendance = $this->getTodayAttendance();

        if (! $attendance) {
            return redirect()->route('attendance.index');
        }

        $status = $this->determineStatus($attendance);

        if ($status !== 'working') {
            return redirect()->route('attendance.index');
        }

        $attendance->update([
            'clock_out_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakIn()
    {
        $attendance = $this->getTodayAttendance();

        if (! $attendance) {
            return redirect()->route('attendance.index');
        }

        $status = $this->determineStatus($attendance);

        if($status !== 'working') {
            return redirect()->route('attendance.index');
        }

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakOut()
    {
        $attendance = $this->getTodayAttendance();

        if (! $attendance) {
            return redirect()->route('attendance.index');
        }

        $status = $this->determineStatus($attendance);

        if ($status !== 'on_break') {
            return redirect()->route('attendance.index');
        }

        $break = $attendance->breaks()
            ->whereNull('break_end_at')
            ->latest('break_start_at')
            ->first();

        if (! $break) {
            return redirect()->route('attendance.index');
        }

        $break->update([
            'break_end_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    private function getTodayAttendance()
    {
        return Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->first();
    }

    private function determineStatus($attendance)
    {
        if (! $attendance) {
            return 'working_out';
        }

        if ($attendance->clock_out_at) {
            return 'finished';
        }

        if ($attendance->breaks()->whereNull('break_end_at')->exists()) {
            return 'on_break';
        }

        return 'working';
    }

    public function list(Request $request)
    {
        Carbon::setLocale('ja');

        $monthParam = $request->query('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m-d', $monthParam . '-01');

        $month = $currentMonth->format('Y/m');

        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breaks', 'attendanceRequests')
            ->where('user_id', Auth::id())
            ->whereBetween('work_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
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

                $request = $attendance->attendanceRequests()
                    ->latest()
                    ->first();

                if ($request) {
                    $requestStatus = match ($request->status) {
                        0 => '申請中',
                        1 => '承認済み',
                        default => '',
                    };
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
            }

            $attendanceList[] = [
                'work_date' => $date->toDateString(),
                'date_label' => $date->format('m/d') . '（' . ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] . '）',
                'clock_in_at' => $clockInAt,
                'clock_out_at' => $clockOutAt,
                'break_time' => $breakTime,
                'work_time' => $workTime,
                'attendance_id' => $attendance ? $attendance->id : null,
                'request_status' => $requestStatus,
                'is_future' => Carbon::parse($date)->isFuture(),
            ];

            $date->addDay();
        }

        return view('attendance.list', compact('month', 'previousMonth', 'nextMonth', 'attendanceList'));
    }

    public function detail($id)
    {
        $date = $id;
        $dateCarbon = Carbon::parse($date)->startOfDay();

        if ($dateCarbon->isFuture()) {
            return redirect()->route('attendance.list');
        }

        $attendance = Attendance::with(['user', 'breaks'])
            ->where('user_id', Auth::id())
            ->whereDate('work_date', $date)
            ->first();

        $isPending = false;

        if ($attendance) {
            $isPending = $attendance->attendanceRequests()
                ->where('status', 0)
                ->exists();
        }

        $isFutureDate = $dateCarbon->isFuture();
        $isTodayAndNotClockedOut = $dateCarbon->isToday() && (!$attendance || !$attendance->clock_out_at);

        $isDisabled = $isPending || $isFutureDate || $isTodayAndNotClockedOut;

        $detail = [
            'name' => Auth::user()->name,
            'year' => Carbon::parse($date)->format('Y年'),
            'date' => Carbon::parse($date)->format('n月j日'),
            'clock_in_at' => '',
            'clock_out_at' => '',
            'note' => '',
            'breaks' => [],
        ];

        if ($attendance) {
            $detail['name'] = $attendance->user->name;
            $detail['clock_in_at'] = $attendance->clock_in_at
                ? Carbon::parse($attendance->clock_in_at)->format('H:i')
                : '';
            $detail['clock_out_at'] = $attendance->clock_out_at
                ? Carbon::parse($attendance->clock_out_at)->format('H:i')
                : '';
            $detail['note'] = $attendance->note ?? '';

            foreach ($attendance->breaks as $break) {
                $detail['breaks'][] = [
                    'break_start_at' => $break->break_start_at
                        ? Carbon::parse($break->break_start_at)->format('H:i')
                        : '',
                    'break_end_at' => $break->break_end_at
                        ? Carbon::parse($break->break_end_at)->format('H:i')
                        : '',
                ];
            }
        }

        return view('attendance.detail', compact('attendance','detail', 'isPending', 'isFutureDate', 'isTodayAndNotClockedOut', 'isDisabled', 'date'));
    }
}
