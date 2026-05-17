<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStampTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_in()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/attendance/clock-in');

        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('work_date', now()->toDateString())
        ->first();

        $this->assertNotNull($attendance->clock_in_at);
    }

    public function test_clock_in_time_is_displayed_on_attendance_list()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => null,
        ]);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');
    }

    public function test_user_can_clock_out()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ]);

        $response = $this->post('/attendance/clock-out');

        $response->assertRedirect();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance->clock_out_at);
    }

    public function test_clock_out_time_is_displayed_on_attendance_list()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('18:00');
    }

    public function test_user_can_start_break()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ]);

        $response = $this->post('/attendance/break-in');

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
        ]);

        $break = \App\Models\AttendanceBreak::where('attendance_id', $attendance->id)
            ->first();

        $this->assertNotNull($break->break_start_at);
        $this->assertNull($break->break_end_at);
    }

    public function test_user_can_end_break()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ]);

        $break = \App\Models\AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => now(),
            'break_end_at' => null,
        ]);

        $response = $this->post('/attendance/break-out');

        $response->assertRedirect();

        $break = \App\Models\AttendanceBreak::where('attendance_id', $attendance->id)
            ->first();

        $this->assertNotNull($break->break_end_at);
    }

    public function test_user_can_take_break_multiple_times_in_a_day()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ]);

        $this->post('/attendance/break-in');
        $this->post('/attendance/break-out');

        $this->post('/attendance/break-in');
        $this->post('/attendance/break-out');

        $this->assertEquals(2, AttendanceBreak::where('attendance_id', $attendance->id)->count());
    }

    public function test_break_time_is_displayed_on_attendance_list()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => now()->setTime(12, 0),
            'break_end_at' => now()->setTime(13, 0),
        ]);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('1:00');
    }
}
