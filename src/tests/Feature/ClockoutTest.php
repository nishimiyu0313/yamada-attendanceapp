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

        // 2. 今日の勤怠データを作成（出勤済み）
        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::parse('09:00:00'),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        // 3. PATCH リクエストで退勤
        $postResponse = $this->actingAs($user)->patch(route('attendance.update', $attendance->id));

        // 4. リダイレクトされることを確認
        $postResponse->assertStatus(302);

        // 5. DB のレコードが更新されていることを確認
        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'user_id' => $user->id,
            'status' => Attendance::STATUS_FINISHED,
        ]);

        // 6. clock_out が設定されていることを確認
        $updatedAttendance = Attendance::find($attendance->id);
        $this->assertNotNull($updatedAttendance->clock_out);
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる() 
    {
        $user = User::factory()->create();

        // 2. 勤怠データ作成
        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::parse('09:00:00'),
            'clock_out'  => Carbon::parse('18:00:00'),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        /** @var \App\Models\User $user */
        // 3. 勤怠一覧画面にアクセス
        $response = $this->actingAs($user)
            ->get(route('attendance.list')); // 勤怠一覧のルート名に合わせて変更

        $response->assertStatus(200);

        // 4. 画面に退勤時刻が表示されているか確認
        $response->assertSee('18:00');
    }
    }
