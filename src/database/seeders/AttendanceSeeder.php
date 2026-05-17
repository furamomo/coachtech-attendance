<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $generalUsers = User::where('is_admin', false)->get();

        $startDate = '2025-12-01';
        $endDate = '2026-03-31';

        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($generalUsers as $user) {
            foreach ($period as $date) {
                if ($date->isWeekend()) {
                    continue;
                }

                $workDate = $date->format('Y-m-d');

                $clockInAt = Carbon::parse($workDate . ' 09:00:00');
                $clockOutAt = Carbon::parse($workDate . ' 18:00:00');

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $workDate,
                    'clock_in_at' => $clockInAt,
                    'clock_out_at' => $clockOutAt,
                    'note' => null,
                ]);

                AttendanceBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start_at' => Carbon::parse($workDate . ' 12:00:00'),
                    'break_end_at' => Carbon::parse($workDate . ' 13:00:00'),
                ]);
            }
        }
    }
}
