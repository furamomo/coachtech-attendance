<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_error_is_displayed_when_clock_in_time_is_after_clock_out_time()
    {
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

        $response = $this->actingAs($user)->post('/attendance/' . $attendance->id . '/request', [
            'clock_in_at' => '19:00',
            'clock_out_at' => '18:00',
            'breaks' => [],
            'note' => '',
        ]);

        $response->assertSessionHasErrors([
            'clock_out_at' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_error_is_displayed_when_break_start_time_is_after_clock_out_time()
    {
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

        $response = $this->actingAs($user)->post('/attendance/' . $attendance->id . '/request', [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [
                [
                    'start' => '19:00',
                    'end' => '19:30',
                ],
            ],
            'note' => '修正理由です',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_error_is_displayed_when_break_end_time_is_after_clock_out_time()
    {
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

        $response = $this->actingAs($user)->post('/attendance/' . $attendance->id . '/request', [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [
                [
                    'start' => '19:00',
                    'end' => '19:30',
                ],
            ],
            'note' => '修正理由です',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_error_is_displayed_when_note_is_empty()
    {
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

        $response = $this->actingAs($user)->post('/attendance/' . '2026-04-10' . '/request', [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'breaks' => [],
            'note' => '',
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }

    public function test_attendance_correction_request_is_created()
    {
        $this->withoutExceptionHandling();


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

        $response = $this->actingAs($user)->post('/attendance/' . '2026-04-10' . '/request', [
            'clock_in_at' => '10:00',
            'clock_out_at' => '19:00',
            'breaks' => [],
            'note' => '電車遅延のため',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'note' => '電車遅延のため',
            'status' => 0,
        ]);
    }

    public function test_pending_requests_are_displayed_on_request_list()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
        ]);

        AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in_at' => '2026-04-10 10:00:00',
            'clock_out_at' => '2026-04-10 19:00:00',
            'note' => '電車遅延のため',
            'status' => 0,
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertSee('電車遅延のため');
    }

    public function test_approved_requests_are_displayed_on_request_list()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
        ]);

        AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in_at' => '2026-04-10 10:00:00',
            'clock_out_at' => '2026-04-10 19:00:00',
            'note' => '電車遅延のため',
            'status' => 1,
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('電車遅延のため');
    }

    public function test_detail_button_navigates_to_attendance_detail_page()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
        ]);

        AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in_at' => '2026-04-10 10:00:00',
            'clock_out_at' => '2026-04-10 19:00:00',
            'note' => '電車遅延のため',
            'status' => 0,
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertSee('詳細');
        $response->assertSee('/attendance/request/detail', false);
    }
}
