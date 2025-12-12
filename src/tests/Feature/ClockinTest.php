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

        // 2. 出勤前の状態で出勤ボタンを押す（POST /attendance）
        $postResponse = $this->actingAs($user)->post(route('attendance.store'));

        // 3. リダイレクトされることを確認
        $postResponse->assertStatus(302);

        // 4. Attendance テーブルにレコードが作成されていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id'   => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'status'    => Attendance::STATUS_WORKING,
            'clock_out' => null,
        ]);

        // 5. 作成されたレコードを取得して clock_in が設定されていることを確認
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($attendance->clock_in);
    }


    public function test_出勤は一日一回のみできる() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 今日の勤怠データを作成（1回目の出勤）
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::parse('09:00:00'),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        // 3. 2回目の出勤を試みる
        $response = $this->actingAs($user)->post(route('attendance.store', ['id' => $user->id]));

        // 4. 2回目はリダイレクトされる（出勤できない）
        $response->assertStatus(302);

        // 5. Attendance テーブルに今日のレコードは1件だけ
        $this->assertEquals(1, Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->count());
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 今日の勤怠データを作成（出勤時刻を固定）
        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::parse('09:00:00'),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        // 3. ユーザーとして勤怠一覧画面にアクセス
        $response = $this->actingAs($user)->get(route('attendance.list'));

        // 4. 出勤時刻が画面に表示されていることを確認
        $response->assertStatus(200)
            ->assertSee('09:00'); // 画面に 09:00 が表示されることを確認
    }

    }
