<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;

class UserdetailupdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('attendance.detailstore', ['id' => $user->id]), [
            'clock_in'  => '10:00',
            'clock_out' => '09:00', // clock_inより後ではない
            'reason'    => '業務開始',
        ]);

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間が不適切です',
        ]);
    }


    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'work_date' => now()->subDay()->toDateString(),
        ]);

        $break = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '12:30:00',
        ]);

        $response = $this->actingAs($user)->post(
            route('attendance.detailstore', $attendance->id),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'reason' => '修正理由',
                'breaks' => [
                    [
                        'id' => $break->id,
                        'start' => '19:00', // 退勤より後
                        'end' => '19:30',
                    ]
                ],
            ]
        );

        // バリデーションエラー確認
        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'work_date' => now()->subDay()->toDateString(),
        ]);

        $break = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '12:30:00',
        ]);

        $response = $this->actingAs($user)->post(route('attendance.detailstore', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '修正理由',
            'breaks' => [
                [
                    'id' => $break->id,
                    'start' => '12:00',
                    'end' => '19:00', // 退勤より後
                ]
            ],
        ]);
        // 4. バリデーションエラーを確認

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. 勤怠データ作成（過去日付にして早期 return を回避）
        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::yesterday(), // 今日でない
            'clock_in'  => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        // 3. 勤怠詳細ページに POST で備考欄を空にして送信
        $response = $this->actingAs($user)->post(
            route('attendance.detailstore', ['id' => $attendance->id]),
            [
                'clock_in'  => '09:00',
                'clock_out' => '18:00',
                'reason'    => '', // 空
                'breaks'    => [
                    ['id' => 1, 'start' => '12:00', 'end' => '12:30'],
                ],
            ]
        );

        // 4. バリデーションエラーがセッションにあるか確認
        $response->assertSessionHasErrors(['reason']);

        // 5. メッセージまで確認
        $this->assertEquals(
            '備考を入力してください',
            session('errors')->get('reason')[0]
        );
    }

    public function test_修正申請処理が実行される()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // ② 勤怠情報作成（任意の日付・時間で固定）
        $attendance = \App\Models\Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2025-12-13',
            'clock_in'  => '2025-12-13 09:00:00',
            'clock_out' => '2025-12-13 18:00:00',
            'status'    => 'finished',
        ]);

        /** @var \App\Models\User $user */

        // ③ 該当ユーザーでログインし、勤怠詳細ページにアクセス
        $response = $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $attendance->id]));

        // ④ Blade 表示と合わせて時刻をフォーマット
        $clockInFormatted  = \Carbon\Carbon::parse($attendance->clock_in)->format('H:i');
        $clockOutFormatted = \Carbon\Carbon::parse($attendance->clock_out)->format('H:i');

        // ⑤ 出勤・退勤の時間が正しく表示されていることを確認
        $response->assertSee($clockInFormatted);
        $response->assertSee($clockOutFormatted);

        // ⑥ ステータスコードも確認
        $response->assertStatus(200);
    }

    public function test_「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);

        $admin = User::factory()->create(['role' => 'admin']);

        // ② 勤怠情報作成
        $attendance = \App\Models\Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2025-12-13',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'status'    => 'finished',
        ]);




        // ③ 修正申請作成（承認待ち）
        $request = \App\Models\Request::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'reason'        => '修正理由',
            'status'        => 'applied',
            'requested_clock_in' => now(),  // 必須なのでセット
            'requested_clock_out' => now()->addHours(8),
            'applied_date' => now(),                // $fillable にあるのでセット可能
            'approver_id' => $admin->id, // 承認待ち
        ]);


        /** @var \App\Models\User $user */
        // ④ ログインユーザーで自分の申請一覧ページにアクセス
        $response = $this->actingAs($user)
            ->get(route('attendance.request', ['status' => 'applied'])); // 承認待ち一覧のルート名

        // ⑤ 作成した申請内容が全て表示されていることを確認
        $response->assertSee('修正理由');

        // ユーザー名も表示される場合
        $response->assertSee($user->name);

        $response->assertStatus(200);
    }

    public function test_「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        $user = User::factory()->create(['name' => 'ユーザー1']);

        // ② 管理者作成
        $admin = User::factory()->create(['role' => 'admin']);

        // ③ 勤怠情報作成
        $attendance = \App\Models\Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2025-12-13',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'status'    => 'finished',
        ]);

        // ④ 修正申請作成（承認済み）
        $request = \App\Models\Request::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'reason'        => '修正理由',
            'status'        => 'approved',
            'requested_clock_in' => now(),  // 必須なのでセット
            'requested_clock_out' => now()->addHours(8),
            'applied_date' => now(),                // $fillable にあるのでセット可能
            'approver_id' => $admin->id, // 承認待ち
        ]);


        /** @var \App\Models\User $user */
        // ⑤ 管理者で承認済み申請一覧ページにアクセス
        $response = $this->actingAs($user)
            ->get(route('attendance.request', ['status' => 'approved'])); // 承認済み一覧のルート名

        // ⑥ 作成した承認済み申請内容が全て表示されることを確認
        $response->assertSee('修正理由');

        // 申請者の名前も表示される場合
        $response->assertSee($user->name);

        $response->assertStatus(200);
    }

    public function test_各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);

        // ② 勤怠情報作成
        $attendance = \App\Models\Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2025-12-13',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'status'    => 'finished',
        ]);

        // ③ 修正申請作成
        $request = \App\Models\Request::create([
            'attendance_id' => $attendance->id,
            'reason'        => '修正理由',
            'status'        => 'approved',
            'requested_clock_in' => now(),  // 必須なのでセット
            'requested_clock_out' => now()->addHours(8),
            'applied_date' => now(),                // $fillable にあるのでセット可能
            'approver_id' => null, // 承認待ち
        ]);

        /** @var \App\Models\User $user */
        $indexResponse = $this->actingAs($user)
            ->get(route('attendance.request')); // 一覧ページ

        // 一覧に詳細リンクがあるか確認
        $indexResponse->assertSee('詳細');



        // ④ 管理者作成

        // 申請一覧ページのルート

        $detailResponse = $this->actingAs($user)
            ->get(route('attendance.detailrequest', $request->id));
        /** @var \Illuminate\Testing\TestResponse $detailResponse */
        // ⑤ 申請詳細リンクが正しい申請IDに向いていることを確認
       



        $detailResponse->assertStatus(200);
        $detailResponse->assertViewIs('user.detail'); // ← Blade名
        $detailResponse->assertViewHas('attendance');
    }
}
