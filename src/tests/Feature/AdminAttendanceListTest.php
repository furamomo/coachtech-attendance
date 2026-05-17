<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_users_attendance_records_are_displayed_on_admin_attendance_list()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $userA */
        $userA = User::factory()->create([
            'name' => '山田太郎',
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $userB */
        $userB = User::factory()->create([
            'name' => '佐藤花子',
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $userA->id,
            'work_date' => '2026-04-27',
            'clock_in_at' => '2026-04-27 09:00:00',
            'clock_out_at' => '2026-04-27 18:00:00',
        ]);

        Attendance::create([
            'user_id' => $userB->id,
            'work_date' => '2026-04-27',
            'clock_in_at' => '2026-04-27 10:00:00',
            'clock_out_at' => '2026-04-27 19:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?day=2026-04-27');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('佐藤花子');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    public function test_current_date_is_displayed_on_admin_attendance_list()
    {
        Carbon::setTestNow(Carbon::parse('2026-04-27'));

        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026/04/27');
    }

    public function test_previous_day_information_is_displayed_when_previous_day_button_is_pressed()
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

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-26',
            'clock_in_at' => '2026-04-26 09:00:00',
            'clock_out_at' => '2026-04-26 18:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?day=2026-04-26');

        $response->assertStatus(200);
        $response->assertSee('2026/04/26');
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_next_day_information_is_displayed_when_next_day_button_is_pressed()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'name' => '佐藤花子',
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-28',
            'clock_in_at' => '2026-04-28 10:00:00',
            'clock_out_at' => '2026-04-28 19:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?day=2026-04-28');

        $response->assertStatus(200);
        $response->assertSee('2026/04/28');
        $response->assertSee('佐藤花子');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }
}