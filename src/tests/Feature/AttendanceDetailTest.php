<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_name_is_displayed_on_attendance_detail_page()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/2026-04-10');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
    }

    public function test_selected_date_is_displayed_on_attendance_detail_page()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/2026-04-10');

        $response->assertStatus(200);
        $response->assertSee('4月10日');
    }

    public function test_clock_in_and_clock_out_time_are_displayed_correctly()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
            'clock_in_at' => '2026-04-10 09:00:00',
            'clock_out_at' => '2026-04-10 18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/2026-04-10');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_break_time_is_displayed_correctly()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-04-10 12:00:00',
            'break_end_at' => '2026-04-10 13:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/2026-04-10');

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
