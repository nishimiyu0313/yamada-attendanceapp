<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;


class ClockoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_退勤ボタンが正しく機能する()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();


        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::parse('09:00:00'),
            'status'    => Attendance::STATUS_WORKING,
        ]);


        $postResponse = $this->actingAs($user)->patch(route('attendance.update', $attendance->id));

        $postResponse->assertStatus(302);


        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'user_id' => $user->id,
            'status' => Attendance::STATUS_FINISHED,
        ]);


        $updatedAttendance = Attendance::find($attendance->id);
        $this->assertNotNull($updatedAttendance->clock_out);
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::parse('09:00:00'),
            'clock_out'  => Carbon::parse('18:00:00'),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('attendance.list'));

        $response->assertStatus(200);

        $response->assertSee('18:00');
    }
}
