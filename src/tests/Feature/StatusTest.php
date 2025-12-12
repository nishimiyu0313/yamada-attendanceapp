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

        // 2. 勤務外の勤怠データを作成（例：status = "out_of_work"）
        $response = $this->actingAs($user)->get(route('attendance.create'));

        // 4. 「勤務外」が表示されていることを確認
        $response->assertStatus(200)
            ->assertSee('勤務外');

    }

    public function test_出勤中の場合、勤怠ステータスが正しく表示される() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 今日の勤怠データを「出勤中」として作成
        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
            'status'    => \App\Models\Attendance::STATUS_WORKING,
            'clock_out' => null, // 出勤中なので null のまま
        ]);

        // 3. ページにアクセス
        $response = $this->actingAs($user)->get(route('attendance.create'));

        // 4. 「出勤中」が表示されているか確認
        $response->assertStatus(200)
            ->assertSee('出勤中');

    }

    public function test_休憩中の場合、勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 今日の勤怠データを「休憩中」として作成
        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
            'status'    => \App\Models\Attendance::STATUS_BREAKING,
            'clock_out' => null, // 休憩中なのでまだ退勤していない
        ]);

        // 3. ページにアクセス
        $response = $this->actingAs($user)->get(route('attendance.create'));

        // 4. 「休憩中」が正しく表示されることを確認
        $response->assertStatus(200)
            ->assertSee('休憩中');

    }

    public function test_退勤済の場合、勤怠ステータスが正しく表示される() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 今日の勤怠データを「退勤済」として作成
        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
            'status'    => \App\Models\Attendance::STATUS_FINISHED, // 何でも良い（clock_out が優先される） null,
            'clock_out' => now(), // ★ 退勤したので必須
        ]);

        // 3. ページにアクセス
        $response = $this->actingAs($user)->get(route('attendance.create'));

        // 4. 「退勤済」が正しく表示されることを確認
        $response->assertStatus(200)
            ->assertSee('退勤済');

    }
}
