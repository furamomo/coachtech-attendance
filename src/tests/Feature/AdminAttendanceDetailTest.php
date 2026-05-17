<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_attendance_data_is_displayed_on_detail_page()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
            'clock_in_at' => '2026-04-10 09:00:00',
            'clock_out_at' => '2026-04-10 18:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_error_is_displayed_when_clock_in_time_is_after_clock_out_time()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
            'clock_in_at' => '2026-04-10 09:00:00',
            'clock_out_at' => '2026-04-10 18:00:00',
        ]);

        $response = $this->actingAs($admin)->post('/admin/attendance/' . $attendance->id . '/update', [
            'clock_in_at' => '19:00',
            'clock_out_at' => '18:00',
            'breaks' => [],
            'note' => '修正理由',
        ]);

        $response->assertSessionHasErrors([
            'clock_out_at' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_error_is_displayed_when_break_start_time_is_after_clock_out_time()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
            'clock_in_at' => '2026-04-10 09:00:00',
            'clock_out_at' => '2026-04-10 18:00:00',
        ]);

        $response = $this->actingAs($admin)->post('/admin/attendance/'.$attendance->id.'/update', [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [
                [
                    'start' => '19:00',
                    'end' => '19:30',
                ],
            ],
            'note' => '修正理由',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_error_is_displayed_when_break_end_time_is_after_clock_out_time()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
            'clock_in_at' => '2026-04-10 09:00:00',
            'clock_out_at' => '2026-04-10 18:00:00',
        ]);

        $response = $this->actingAs($admin)->post('/admin/attendance/'.$attendance->id.'/update', [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [
                [
                    'start' => '17:30',
                    'end' => '19:00',
                ],
            ],
            'note' => '修正理由',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_error_is_displayed_when_note_is_empty()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
        ]);

        $response = $this->actingAs($admin)->post('/admin/attendance/'.$attendance->id.'/update', [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [],
            'note' => '',
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
