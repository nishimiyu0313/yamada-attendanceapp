<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;


class ClockinTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤ボタンが正しく機能する()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $postResponse = $this->actingAs($user)->post(route('attendance.store'));


        $postResponse->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'user_id'   => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'status'    => Attendance::STATUS_WORKING,
            'clock_out' => null,
        ]);

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($attendance->clock_in);
    }


    public function test_出勤は一日一回のみできる()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::parse('09:00:00'),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $response = $this->actingAs($user)->post(route('attendance.store', ['id' => $user->id]));

        $response->assertStatus(302);

        $this->assertEquals(1, Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->count());
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::parse('09:00:00'),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200)
            ->assertSee('09:00');
    }
}
