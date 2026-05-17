<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceApproveTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_requests_are_displayed()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

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
            'clock_in_at' => '10:00',
            'clock_out_at' => '19:00',
            'note' => '修正理由',
            'status' => 0,
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
    }

    public function test_approved_requests_are_displayed()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

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
            'clock_in_at' => '10:00',
            'clock_out_at' => '19:00',
            'note' => '修正理由',
            'status' => 1,
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
    }

    public function test_request_detail_is_displayed()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
        ]);

        $request = AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in_at' => '10:00',
            'clock_out_at' => '19:00',
            'note' => '修正理由',
            'status' => 0,
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/approve/' . $request->id);

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    public function test_admin_can_approve_request()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
        ]);

        $request = AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in_at' => '10:00',
            'clock_out_at' => '19:00',
            'note' => '修正理由',
            'status' => 0,
        ]);

        $response = $this->actingAs($admin)->post('/stamp_correction_request/approve/' . $request->id);

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'status' => 1,
        ]);
    }
}
