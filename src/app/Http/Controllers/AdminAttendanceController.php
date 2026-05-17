<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Carbon\CarbonPeriod;
use Symfony\Component\HttpFoundation\StreamedResponse;


class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $day = $request->input('day', Carbon::today()->format('Y-m-d'));

        $currentDay = Carbon::parse($day);
        $previousDay = $currentDay->copy()->subDay()->format('Y-m-d');
        $nextDay = $currentDay->copy()->addDay()->format('Y-m-d');

        $date = $currentDay->format('Y年n月j日');
        $displayDay = $currentDay->format('Y/m/d');

        $users = User::where('is_admin', false)->get();

        $attendances = Attendance::with(['user', 'breaks', 'attendanceRequests'])
            ->whereDate('work_date', $day)
            ->get()
            ->keyBy('user_id');

        $dayAttendanceList = $users->map(function($user) use ($attendances, $day) {

            $attendance = $attendances->get($user->id);

            if(!$attendance) {
                return (object) [
                    'id' => null,
                    'user_id' => $user->id,
                    'work_date' => $day,
                    'name' => $user->name,
                    'clock_in_at' => '',
                    'clock_out_at' => '',
                    'break_time' => '',
                    'work_time' => '',
                    'request_status' => '',
                ];
            }

            $breakMinutes = $attendance->breaks->sum(function ($break) {
                if (!$break->break_start_at || !$break->break_end_at) {
                    return 0;
                }

                return Carbon::parse($break->break_start_at)
                    ->diffInMinutes(Carbon::parse($break->break_end_at));
            });

            $workMinutes = 0;

            if ($attendance->clock_in_at && $attendance->clock_out_at) {
                $totalMinutes = Carbon::parse($attendance->clock_in_at)
                    ->diffInMinutes(Carbon::parse($attendance->clock_out_at));

                $workMinutes = $totalMinutes - $breakMinutes;
            }

            $latestRequest = $attendance->attendanceRequests->sortByDesc('created_at')->first();

            if (!$latestRequest) {
                $requestStatus = '';
            } elseif ($latestRequest->status === \App\Models\AttendanceRequest::STATUS_PENDING) {
                $requestStatus = '申請中';
            } elseif ($latestRequest->status === \App\Models\AttendanceRequest::STATUS_APPROVED) {
                $requestStatus = '承認済み';
            } else {
                $requestStatus = '';
            }

            return (object) [
                'id' => $attendance->id,
                'user_id' => $user->id,
                'work_date' => $day,
                'name' => $attendance->user->name,
                'clock_in_at' => $attendance->clock_in_at
                    ? Carbon::parse($attendance->clock_in_at)->format('H:i')
                    : '',
                'clock_out_at' => $attendance->clock_out_at
                    ? Carbon::parse($attendance->clock_out_at)->format('H:i')
                    : '',
                'break_time' => $attendance->breaks->isNotEmpty()
                    ? sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60)
                    : '',

                'work_time' => ($attendance->clock_in_at && $attendance->clock_out_at)
                    ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60)
                    : '',
                'request_status' => $requestStatus,
            ];
        });

        return view('admin.attendance.list', compact('currentDay', 'date', 'displayDay', 'previousDay', 'nextDay', 'dayAttendanceList'));
    }

    public function detail($id) {

        $attendance = Attendance::with('user', 'breaks', 'attendanceRequests')
            ->findOrFail($id);

        $date = Carbon::parse($attendance->work_date);

        $latestRequest = $attendance->attendanceRequests->sortByDesc('created_at')->first();

        $detail = [
            'name' => $attendance->user->name,
            'year' => $date->format('Y年'),
            'date' => $date->format('m月j日'),
            'clock_in_at' => $attendance->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('H:i') : '',
            'clock_out_at' => $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('H:i') : '',
            'breaks' => $attendance->breaks->map(function($break){
                return[
                    'break_start_at' => $break->break_start_at ? Carbon::parse($break->break_start_at)->format('H:i') : '',
                    'break_end_at' => $break->break_end_at ? Carbon::parse($break->break_end_at)->format('H:i') : '',
                ];
            })->toArray(),
            'note' => $attendance->note,
        ];

        $isPending = $latestRequest && !$latestRequest->isApproved();

        $isWorking = $attendance->clock_in_at && !$attendance->clock_out_at && $date->isToday();

        $isDisabled = $isPending || $isWorking;

        return view('admin.attendance.detail', compact( 'attendance', 'detail', 'isPending', 'isWorking','isDisabled' ));
    }

    public function update(AttendanceCorrectionRequest $request, $id) {

        $attendance = Attendance::with('breaks')->findOrFail($id);
        $work_date = \Carbon\Carbon::parse($attendance->work_date)->toDateString();

        $attendance->update([
            'clock_in_at' => $work_date . ' ' . $request->clock_in_at,
            'clock_out_at' => $work_date . ' ' . $request->clock_out_at,
            'note' => $request->note,
        ]);

        $attendance->breaks()->delete();

        foreach ($request->breaks as $break) {
            if(!empty($break['start']) && !empty($break['end'])) {
                $attendance->breaks()->create([
                    'break_start_at' => $work_date . ' ' .$break['start'],
                    'break_end_at' => $work_date . ' ' .$break['end'],
                ]);
            }
        }

        return redirect()->route('admin.attendance.detail',['id' => $attendance->id]);
    }

    public function create($user_id, $work_date) {

        $user = User::findOrFail($user_id);
        $date = Carbon::parse($work_date);
        $today = Carbon::now()->toDateString();

        $attendance = Attendance::where('user_id', $user_id)
            ->where('work_date', $date->toDateString())
            ->first();

        if($attendance) {
            return redirect()->route('admin.attendance.detail', ['id' => $attendance->id]);
        }

        $detail = [
            'name' => $user->name,
            'work_date' => $work_date,
            'year' => $date->format('Y年'),
            'date' => $date->format('m月j日'),
            'clock_in_at' => '',
            'clock_out_at' => '',
            'breaks' => [
                ['break_start_at' => '', 'break_end_at' => '']
            ],
            'note' => '',
        ];

        $isToday = $date->isToday();

        return view('admin.attendance.create', compact('date', 'user', 'detail' , 'isToday'));
    }

    public function store(AttendanceCorrectionRequest $request, $user_id, $work_date) {

        $attendance = Attendance::create([
            'user_id' => $user_id,
            'work_date' => $work_date,
            'clock_in_at' => $request->clock_in_at,
            'clock_out_at' => $request->clock_out_at,
            'note' => $request->note,
        ]);

        $breakStart = $request->breaks[0]['start'] ?? '';
        $breakEnd = $request->breaks[0]['end'] ?? '';

        if (!empty($breakStart) && !empty($breakEnd)) {
            $attendance->breaks()->create([
                'break_start_at' => $work_date . ' ' . $breakStart,
                'break_end_at' => $work_date . ' ' . $breakEnd,
            ]);
        }

        return redirect()->route('admin.attendance.detail', ['id' => $attendance->id]);
    }

    public function exportCsv(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $monthParam = $request->query('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m-d', $monthParam . '-01');

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $staff->id)
            ->whereBetween('work_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->orderBy('work_date')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->toDateString();
            });

        $fileName = $staff->name . '_' . $currentMonth->format('Y_m') . '_attendance.csv';

        $response = new StreamedResponse(function () use ($startOfMonth, $endOfMonth, $attendances, $staff) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['ユーザーID', 'ユーザー名', '日付', '出勤', '退勤', '休憩', '合計']);

            $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

            foreach ($period as $date) {
                $dateKey = $date->toDateString();
                $attendance = $attendances->get($dateKey);

                if (!$attendance) {
                    fputcsv($handle, [
                        $staff->id,
                        $staff->name,
                        $date->format('m/d') . '(' . $date->isoFormat('ddd') . ')',
                        '',
                        '',
                        '',
                        '',
                    ]);
                    continue;
                }

                $clockIn = $attendance->clock_in_at
                    ? Carbon::parse($attendance->clock_in_at)->format('H:i')
                    : '';

                $clockOut = $attendance->clock_out_at
                    ? Carbon::parse($attendance->clock_out_at)->format('H:i')
                    : '';

                $breakMinutes = 0;
                foreach ($attendance->breaks as $break) {
                    if ($break->break_start_at && $break->break_end_at) {
                        $breakMinutes += Carbon::parse($break->break_start_at)
                            ->diffInMinutes(Carbon::parse($break->break_end_at));
                    }
                }

                $breakTime = $attendance->breaks->isNotEmpty()
                    ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT)
                    : '';

                $workMinutes = '';
                if ($attendance->clock_in_at && $attendance->clock_out_at) {
                    $totalMinutes = Carbon::parse($attendance->clock_in_at)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out_at)) - $breakMinutes;

                    $workMinutes = floor($totalMinutes / 60) . ':' . str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT);
                }

                fputcsv($handle, [
                    $staff->id,
                    $staff->name,
                    $date->format('m/d') . '(' . $date->isoFormat('ddd') . ')',
                    $clockIn,
                    $clockOut,
                    $breakTime,
                    $workMinutes,
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="' . rawurlencode($fileName) . '"'
        );

        return $response;
    }
}
