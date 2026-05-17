<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_users()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);



        /** @var \App\Models\User $userA */
        $userA = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'test1@example.com',
            'email_verified_at' => now(),
        ]);

        /** @var \App\Models\User $userB */
        $userB = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'test2@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertSee('山田太郎');
        $response->assertSee('佐藤花子');
        $response->assertSee('test1@example.com');
        $response->assertSee('test2@example.com');
    }

    public function test_user_attendance_is_displayed()
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

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-10',
            'clock_in_at' => '2026-04-10 09:00:00',
            'clock_out_at' => '2026-04-10 18:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/'.$user->id);

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('9:00');
        $response->assertSee('18:00');
    }

    public function test_previous_month_is_displayed_when_previous_button_is_pressed()
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

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' .$user->id.'?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('2026/03');
    }

    public function test_next_month_is_displayed_when_next_button_is_pressed()
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

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id .'?month=2026-05');

        $response->assertStatus(200);
        $response->assertSee('2026/05');
    }

    public function test_detail_button_navigates_to_attendance_detail_page()
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
            'clock_in_at' => '2026-04-10 09:00:00',
            'clock_out_at' => '2026-04-10 18:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/'.$attendance->id);

        $response->assertStatus(200);
        $response->assertSee('/admin/attendance/' . $attendance->id, false);
    }
}
