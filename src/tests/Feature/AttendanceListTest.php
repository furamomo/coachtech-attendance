<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_all_own_attendance_records()
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

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-11',
            'clock_in_at' => '2026-04-11 10:00:00',
            'clock_out_at' => '2026-04-11 19:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-04');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    public function test_current_month_is_displayed_when_attendance_list_page_is_opened()
    {
        Carbon::setTestNow(Carbon::parse('2026-04-15'));

        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026/04');
    }

    public function test_previous_month_information_is_displayed_when_previous_month_button_is_pressed()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-10',
            'clock_in_at' => '2026-03-10 09:00:00',
            'clock_out_at' => '2026-03-10 18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('2026/03');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_next_month_information_is_displayed_when_next_month_button_is_pressed()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-10',
            'clock_in_at' => '2026-05-10 09:00:00',
            'clock_out_at' => '2026-05-10 18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-05');

        $response->assertStatus(200);
        $response->assertSee('2026/05');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_detail_button_navigates_to_attendance_detail_page()
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

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-04');

        $response->assertStatus(200);
        $response->assertSee('詳細');
        $response->assertSee('/attendance/detail/2026-04-10', false);
    }
}
