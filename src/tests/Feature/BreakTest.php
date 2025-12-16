<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_休憩ボタンが正しく機能する()
    {
        /** @var \App\Models\User $user */

        $user = User::factory()->create();


        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now(),
            'status'    => Attendance::STATUS_WORKING,
        ]);


        $postBreakResponse = $this->actingAs($user)
            ->post(route('break.store', $attendance->id));

        $postBreakResponse->assertStatus(302);


        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'status' => Attendance::STATUS_BREAKING,
        ]);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_end'     => null,
        ]);


        $latestBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        $patchBreakResponse = $this->actingAs($user)
            ->patch(route('break.update', [$attendance->id, $latestBreak->id]));

        $patchBreakResponse->assertStatus(302);


        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'status' => Attendance::STATUS_WORKING,
        ]);


        $this->assertNotNull(BreakTime::find($latestBreak->id)->break_end);
    }

    public function test_休憩は一日に何回でもできる()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();


        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user)
            ->post(route('break.store', ['attendance_id' => $attendance->id]));

        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_BREAKING, $attendance->status);

        $firstBreak = BreakTime::where('attendance_id', $attendance->id)
            ->latest('id')
            ->first();
        $this->assertNull($firstBreak->break_end);

        $this->actingAs($user)
            ->patch(route('break.update', [
                'attendance_id' => $attendance->id,
                'break_id'      => $firstBreak->id,
            ]));

        $attendance->refresh();
        $firstBreak->refresh();

        $this->assertEquals(Attendance::STATUS_WORKING, $attendance->status);
        $this->assertNotNull($firstBreak->break_end);


        $this->actingAs($user)
            ->post(route('break.store', ['attendance_id' => $attendance->id]));

        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_BREAKING, $attendance->status);

        $secondBreak = BreakTime::where('attendance_id', $attendance->id)
            ->latest('id')
            ->first();

        $this->assertNotEquals($firstBreak->id, $secondBreak->id);
        $this->assertNull($secondBreak->break_end);
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now(),
            'status'    => Attendance::STATUS_WORKING,
        ]);


        $postBreakResponse = $this->actingAs($user)
            ->post(route('break.store', $attendance->id));

        $postBreakResponse->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'status' => Attendance::STATUS_BREAKING,
        ]);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_end'     => null,
        ]);


        $latestBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        $patchBreakResponse = $this->actingAs($user)
            ->patch(route('break.update', [$attendance->id, $latestBreak->id]));

        $patchBreakResponse->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->assertNotNull(BreakTime::find($latestBreak->id)->break_end);
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        for ($i = 0; $i < 3; $i++) {

            $this->actingAs($user)
                ->post(route('break.store', $attendance->id));

            $this->assertDatabaseHas('attendances', [
                'id'     => $attendance->id,
                'status' => Attendance::STATUS_BREAKING,
            ]);

            $latestBreak = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest()
                ->first();

            $this->assertNotNull($latestBreak);


            $this->actingAs($user)
                ->patch(route('break.update', [$attendance->id, $latestBreak->id]));

            $this->assertDatabaseHas('attendances', [
                'id'     => $attendance->id,
                'status' => Attendance::STATUS_WORKING,
            ]);

            $this->assertNotNull(BreakTime::find($latestBreak->id)->break_end);
        }

        $this->assertEquals(3, BreakTime::where('attendance_id', $attendance->id)->count());
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $break1 = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => Carbon::parse('12:00'),
            'break_end'     => Carbon::parse('12:30'),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => Carbon::parse('15:00'),
            'break_end'     => Carbon::parse('15:15'),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.list'));

        $response->assertStatus(200);

        $response->assertSee('0:45');
    }
}
