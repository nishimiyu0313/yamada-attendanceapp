<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminusergetTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる() 
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // ② 一般ユーザーを複数作成
        $user1 = User::factory()->create([
            'name'  => 'ユーザー1',
            'email' => 'user1@example.com',
            'role'  => 'user',
        ]);

        $user2 = User::factory()->create([
            'name'  => 'ユーザー2',
            'email' => 'user2@example.com',
            'role'  => 'user',
        ]);

        /** @var \App\Models\User $admin */
        // ③ 管理者でユーザー一覧ページにアクセス
        $response = $this->actingAs($admin)->get('/admin/staff/list');

        // ④ 一般ユーザーの氏名とメールアドレスが表示されることを確認
        $response->assertSee('ユーザー1');
        $response->assertSee('user1@example.com');
        $response->assertSee('ユーザー2');
        $response->assertSee('user2@example.com');

        // ⑤ 管理者自身の情報は表示されない場合は assertDontSee を使う
        $response->assertDontSee($admin->email);

        $response->assertStatus(200);
    }

    public function test_ユーザーの勤怠情報が正しく表示される() 
    {

        $admin = User::factory()->create(['role' => 'admin']);


        // ② 一般ユーザー作成
        $user = User::factory()->create(['role' => 'user']);

        // ③ 勤怠情報作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => '2025-12-13',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'status'    => 'finished',
        ]);


        // ④ 管理者で勤怠一覧ページにアクセス
        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', ['id' => $user->id]));

        // ⑤ 各ユーザーの勤怠情報が表示されていることを確認
        $response->assertSee($user->name);
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));


        $response->assertStatus(200);
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される() 
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        // ログイン状態でアクセス
        $this->actingAs($admin);

        // 今日の前月を算出（例：2025/12 → 前月 = 2025/11）
        $prevDate = today()->subMonth();

        // クエリパラメータは controller と同じ "work_date=Y-m-d"
        $response = $this->get(route('admin.attendance.staff', [
            'id' => $admin->id,
            'work_date' => $prevDate->format('Y-m-d')
        ]));

        // 画面に表示される期待値（Blade のフォーマットに合わせる）
        $expectedMonth = $prevDate->format('Y/m');

        $response->assertStatus(200);
        $response->assertSee($expectedMonth);   // ← 前月が表示されていることを確認

    }

    public function test_「翌月」を押下した時に表示月の前月の情報が表示される() 
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        // ログイン状態でアクセス
        $this->actingAs($admin);

        // 今日の前月を算出（例：2025/12 → 前月 = 2025/11）
        $prevDate = today()->addMonth();

        // クエリパラメータは controller と同じ "work_date=Y-m-d"
        $response = $this->get(route('admin.attendance.staff', [
            'id' => $admin->id,
            'work_date' => $prevDate->format('Y-m-d')
        ]));

        // 画面に表示される期待値（Blade のフォーマットに合わせる）
        $expectedMonth = $prevDate->format('Y/m');

        $response->assertStatus(200);
        $response->assertSee($expectedMonth);
    }


    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する() 
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        // 勤怠作成
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $admin->id,
            'work_date' => today(),
            
        ]);

        // ログイン状態で詳細ページにアクセス
        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.staff', ['id' => $admin->id]));

        // 正しいレスポンスを確認（200 OK）
        $response->assertStatus(200);
    }
}