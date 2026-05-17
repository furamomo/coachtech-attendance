<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_date_is_displayed_on_attendance_page()
    {
        Carbon::setTestNow(Carbon::parse('2026-04-27 09:30:00'));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('2026年4月27日');
    }

    public function test_status_is_working_outside_when_user_has_not_clocked_in()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_status_is_working_when_user_has_clocked_in()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_status_is_finished_when_user_has_clocked_out()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    public function test_status_is_break_when_user_is_taking_break()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => null,
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => now()->setTime(12, 0),
            'break_end_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }
}
