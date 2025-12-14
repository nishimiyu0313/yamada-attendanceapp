<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminattendancelistTest extends TestCase
{
    use RefreshDatabase;

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる() 
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

     
        // 一般ユーザーを複数作成
        $users = User::factory()->count(3)->create(['role' => 'user']);
        $targetDate = Carbon::today();

        foreach ($users as $user) {
        // それぞれのユーザーに勤怠を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => now()->subHours(9),
            'clock_out' => now(),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        // BreakTime を追加
        BreakTime::factory()->count(2)->create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->subHours(5),
            'break_end' => now()->subHours(4),
        ]);
    }

        // ページにアクセス
        $response = $this->get('/admin/attendance/list?work_date=' . $targetDate->format('Y-m-d'));

        $response->assertStatus(200);

        // 作成したユーザーの名前が画面に表示されているか確認
        foreach ($users as $user) {
            $response->assertSee($user->name);
        }

        // 勤怠の時刻や休憩も確認（フォーマットは Blade に合わせる）
        $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i')); // 出勤
        $response->assertSee(\Carbon\Carbon::parse($attendance->clock_out)->format('H:i'));  // 退勤
        $response->assertSee(formatMinutes($attendance->work_minutes_total));
        $response->assertSee(formatMinutes($attendance->break_minutes_total));  // break_minutes_total が formatMinutes() で変換される場合
    }

    public function test_遷移した際に現在の日付が表示される() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        // ログインして勤怠一覧ページへアクセス
        $response = $this->actingAs($user)->get('/admin/attendance/list');

        $currentDate = \Carbon\Carbon::today();
        $expectedDate = $currentDate->format('Y/m/d');

        $response->assertStatus(200);

        $response->assertSee($expectedDate);
    }

    public function test_「前日」を押下した時に前の日の勤怠情報が表示される() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $prevDate = Carbon::today()->subDay();
        $expectedDate = $prevDate->format('Y年m月d日');

        // クエリパラメータを文字列として渡す
        $response = $this->get('/admin/attendance/list?work_date=' . $prevDate->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertSee($expectedDate);
    }

    public function test_「翌日」を押下した時に次の日の勤怠情報が表示される() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $nextDate = Carbon::today()->addDay();
        $expectedDate = $nextDate->format('Y年m月d日');

        $response = $this->get('/admin/attendance/list?work_date=' . $nextDate->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertSee($expectedDate);
    }
}
