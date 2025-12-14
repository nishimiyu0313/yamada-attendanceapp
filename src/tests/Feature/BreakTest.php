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

        // 2. 出勤済のAttendance作成
        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        // ======================
        // 休憩開始
        // ======================
        $postBreakResponse = $this->actingAs($user)
            ->post(route('break.store', $attendance->id));

        $postBreakResponse->assertStatus(302); // リダイレクト確認

        // Attendanceがbreakingになったか
        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'status' => Attendance::STATUS_BREAKING,
        ]);

        // BreakTimeレコードが作られたか
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_end'     => null,
        ]);

        // ======================
        // 休憩終了
        // ======================
        $latestBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        $patchBreakResponse = $this->actingAs($user)
            ->patch(route('break.update', [$attendance->id, $latestBreak->id]));

        $patchBreakResponse->assertStatus(302);

        // Attendanceがworkingに戻ったか
        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'status' => Attendance::STATUS_WORKING,
        ]);

        // BreakTimeのbreak_endがセットされたか
        $this->assertNotNull(BreakTime::find($latestBreak->id)->break_end);
    }

    public function test_休憩は一日に何回でもできる() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 出勤済のAttendance作成
        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        // ======================
        // 1回目の休憩開始
        // ======================
        $this->actingAs($user)
            ->post(route('break.store', ['attendance_id' => $attendance->id]));

        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_BREAKING, $attendance->status);

        $firstBreak = BreakTime::where('attendance_id', $attendance->id)
            ->latest('id') // ID順で最新を取得
            ->first();
        $this->assertNull($firstBreak->break_end);

        // 休憩終了
        $this->actingAs($user)
            ->patch(route('break.update', [
                'attendance_id' => $attendance->id,
                'break_id'      => $firstBreak->id,
            ]));

        $attendance->refresh();
        $firstBreak->refresh();

        $this->assertEquals(Attendance::STATUS_WORKING, $attendance->status);
        $this->assertNotNull($firstBreak->break_end);

        // ======================
        // 2回目の休憩開始
        // ======================
        $this->actingAs($user)
            ->post(route('break.store', ['attendance_id' => $attendance->id]));

        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_BREAKING, $attendance->status);

        $secondBreak = BreakTime::where('attendance_id', $attendance->id)
            ->latest('id')
            ->first();

        // ID が別であることを確認
        $this->assertNotEquals($firstBreak->id, $secondBreak->id);
        $this->assertNull($secondBreak->break_end);
    }

    public function test_休憩戻ボタンが正しく機能する() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 出勤済みAttendance作成
        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        // ======================
        // 休憩開始
        // ======================
        $postBreakResponse = $this->actingAs($user)
            ->post(route('break.store', $attendance->id));

        $postBreakResponse->assertStatus(302); // リダイレクト確認

        // Attendanceがbreakingになったか
        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'status' => Attendance::STATUS_BREAKING,
        ]);

        // BreakTimeレコードが作られたか
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_end'     => null,
        ]);

        // ======================
        // 休憩終了（休憩戻ボタン）
        // ======================
        $latestBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        $patchBreakResponse = $this->actingAs($user)
            ->patch(route('break.update', [$attendance->id, $latestBreak->id]));

        $patchBreakResponse->assertStatus(302); // リダイレクト確認

        // Attendanceがworkingに戻ったか
        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'status' => Attendance::STATUS_WORKING,
        ]);

        // BreakTimeのbreak_endがセットされたか
        $this->assertNotNull(BreakTime::find($latestBreak->id)->break_end);

    }

    public function test_休憩戻は一日に何回でもできる() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 出勤済みAttendance作成
        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        // 休憩開始 → 戻る を 3 回繰り返す
        for ($i = 0; $i < 3; $i++) {
            // 休憩開始
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

            // 休憩終了（休憩戻ボタン）
            $this->actingAs($user)
                ->patch(route('break.update', [$attendance->id, $latestBreak->id]));

            $this->assertDatabaseHas('attendances', [
                'id'     => $attendance->id,
                'status' => Attendance::STATUS_WORKING,
            ]);

            $this->assertNotNull(BreakTime::find($latestBreak->id)->break_end);
        }

        // BreakTimeが3件作られていることを確認
        $this->assertEquals(3, BreakTime::where('attendance_id', $attendance->id)->count());
    
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 出勤済みAttendance作成
        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'status'    => Attendance::STATUS_WORKING,
        ]);

        // 3. 休憩開始・終了
        $break1 = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => Carbon::parse('12:00'),
            'break_end'     => Carbon::parse('12:30'),
        ]);

        // 休憩2: 15:00〜15:15 → 15分
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
