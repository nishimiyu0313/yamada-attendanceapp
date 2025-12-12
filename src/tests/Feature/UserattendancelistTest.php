<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;

class UserattendancelistTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分が行った勤怠情報が全て表示されている() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 当該ユーザーの勤怠データを作成
        $attendances = Attendance::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        // 他ユーザーの勤怠データ（表示されないことを確認するため）
        Attendance::factory()->create();

        // 2. ログインして勤怠一覧ページへアクセス
        $response = $this->actingAs($user)->get(route('attendance.list'));

        // 3. 自分の勤怠情報がすべて表示されていることを確認
        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->date);
            $response->assertSee($attendance->start_time);
            $response->assertSee($attendance->end_time);
        }

        // 他ユーザーの勤怠が表示されていないことも確認（任意）
        $other = Attendance::firstWhere('user_id', '!=', $user->id);
        $response->assertDontSee($other->date);

        //user_idがfactoryにないことが問題
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // ログインして勤怠一覧ページへアクセス
        $response = $this->actingAs($user)->get(route('attendance.list'));

        $currentDate = \Carbon\Carbon::today();
        $currentMonth = $currentDate->format('Y/m');

        $response->assertStatus(200);

        $response->assertSee($currentMonth);
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される() 
    {

        /** @var \App\Models\User $user */
        $user = \App\Models\User::factory()->create();

        // ログイン状態でアクセス
        $this->actingAs($user);

        // 今日の前月を算出（例：2025/12 → 前月 = 2025/11）
        $prevDate = today()->subMonth();

        // クエリパラメータは controller と同じ "work_date=Y-m-d"
        $response = $this->get(route('attendance.list', [
            'work_date' => $prevDate->format('Y-m-d')]));

        // 画面に表示される期待値（Blade のフォーマットに合わせる）
        $expectedMonth = $prevDate->format('Y/m');

        $response->assertStatus(200);
        $response->assertSee($expectedMonth);   // ← 前月が表示されていることを確認
    }

    public function test_「翌月」を押下した時に表示月の前月の情報が表示される() 
    {
        /** @var \App\Models\User $user */
        $user = \App\Models\User::factory()->create();

        // ログイン状態でアクセス
        $this->actingAs($user);

        // 今日の前月を算出（例：2025/12 → 前月 = 2025/11）
        $prevDate = today()->addMonth();

        // クエリパラメータは controller と同じ "work_date=Y-m-d"
        $response = $this->get(route('attendance.list', [
            'work_date' => $prevDate->format('Y-m-d')
        ]));

        // 画面に表示される期待値（Blade のフォーマットに合わせる）
        $expectedMonth = $prevDate->format('Y/m');

        $response->assertStatus(200);
        $response->assertSee($expectedMonth);
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する() 
    {
        /** @var \App\Models\User $user */
        $user = \App\Models\User::factory()->create();

        // 勤怠作成
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        // ログイン状態で詳細ページにアクセス
        $response = $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $attendance->id]));

        // 正しいレスポンスを確認（200 OK）
        $response->assertStatus(200);
    }
}
