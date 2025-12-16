<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤務外の場合、勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.create'));


        $response->assertStatus(200)
            ->assertSee('勤務外');
    }

    public function test_出勤中の場合、勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
            'status'    => \App\Models\Attendance::STATUS_WORKING,
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertStatus(200)
            ->assertSee('出勤中');
    }

    public function test_休憩中の場合、勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
            'status'    => \App\Models\Attendance::STATUS_BREAKING,
            'clock_out' => null,
        ]);


        $response = $this->actingAs($user)->get(route('attendance.create'));

        $response->assertStatus(200)
            ->assertSee('休憩中');
    }

    public function test_退勤済の場合、勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
            'status'    => \App\Models\Attendance::STATUS_FINISHED,
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.create'));


        $response->assertStatus(200)
            ->assertSee('退勤済');
    }
}
