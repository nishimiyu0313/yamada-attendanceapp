<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminattendanceupdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_承認待ちの修正申請が全て表示されている() 
    {

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 2. 一般ユーザーを2人作成
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // 3. Attendance を作成
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
        ]);

        // 4. 承認待ち Request を作成（日時をテスト側で上書き）
        $req1 = Request::factory()->create([
            'attendance_id'       => $attendance1->id,
            'requested_clock_in'  => Carbon::today()->setTime(9, 0),
            'requested_clock_out' => Carbon::today()->setTime(18, 0),
            'status'              => 'applied',
            'reason'              => 'テスト申請1',
            'applied_date'        => now(),
            'approver_id'         => null,
        ]);

        $req2 = Request::factory()->create([
            'attendance_id'       => $attendance2->id,
            'requested_clock_in'  => Carbon::today()->setTime(9, 0),
            'requested_clock_out' => Carbon::today()->setTime(18, 0),
            'status'              => 'applied',
            'reason'              => 'テスト申請2',
            'applied_date'        => now(),
            'approver_id'         => null,
        ]);

        // 5. 承認済み Request
        $approved = Request::factory()->create([
            'attendance_id'       => $attendance1->id,
            'requested_clock_in'  => Carbon::today()->setTime(9, 0),
            'requested_clock_out' => Carbon::today()->setTime(18, 0),
            'status'              => 'approved',
            'reason'              => '承認済み',
            'applied_date'        => now(),
            'approver_id'         => $admin->id,
        ]);


        /** @var \App\Models\User $admin */
        // 6. 管理者で承認待ちタブへアクセス
        $response = $this->actingAs($admin)
            ->get(route('admin.requests', ['status' => 'applied']));

        $response->assertStatus(200);

        // 7. 承認待ちのユーザー名と理由が表示される
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertSee($req1->reason);
        $response->assertSee($req2->reason);


    }

    public function test_承認済みの修正申請が全て表示されている() 
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 一般ユーザーを2人作成
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Attendance を作成
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
        ]);

        // 承認済み Request を作成
        $approved1 = Request::factory()->create([
            'attendance_id'       => $attendance1->id,
            'requested_clock_in'  => Carbon::today()->setTime(9, 0),
            'requested_clock_out' => Carbon::today()->setTime(18, 0),
            'status'              => 'approved',
            'reason'              => '承認済み1',
            'applied_date'        => now(),
            'approver_id'         => $admin->id,
        ]);

        $approved2 = Request::factory()->create([
            'attendance_id'       => $attendance2->id,
            'requested_clock_in'  => Carbon::today()->setTime(9, 0),
            'requested_clock_out' => Carbon::today()->setTime(18, 0),
            'status'              => 'approved',
            'reason'              => '承認済み2',
            'applied_date'        => now(),
            'approver_id'         => $admin->id,
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)
            ->get(route('admin.requests', ['status' => 'approved']));

        $response->assertStatus(200);

        // 承認済みの理由が画面に表示される
        $response->assertSee($approved1->reason);
        $response->assertSee($approved2->reason);

        // もし承認待ちが混ざっている場合は表示されないことを確認（オプション）
        $pending = Request::factory()->create([
            'attendance_id'       => $attendance1->id,
            'requested_clock_in'  => Carbon::today()->setTime(10, 0),
            'requested_clock_out' => Carbon::today()->setTime(19, 0),
            'status'              => 'applied',
            'reason'              => '承認待ちテスト',
            'applied_date'        => now(),
            'approver_id'         => null,
        ]);

        $response->assertDontSee($pending->reason);
    }

    public function test_修正申請の詳細内容が正しく表示されている() 
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 一般ユーザー作成
        $user = User::factory()->create();

        // Attendance 作成
        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => '2025-12-13',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // Request 作成（承認待ち）
        $request = Request::factory()->for($attendance)->create([
            'requested_clock_in'  => Carbon::createFromTime(9, 0, 0),
            'requested_clock_out' => Carbon::createFromTime(18, 0, 0),
            'applied_date'        => '2025-12-13',
            'status'              => 'applied',
            'approver_id'         => null,
        ]);

    // 管理者で詳細ページへアクセス
        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)
            ->get(route('admin.stamp_request.approve', $request->id));

        // 申請内容が正しく表示されることを確認
        $response->assertSee($user->name);
        $response->assertSee(\Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日'));

        // requested_clock_in / requested_clock_out は 'HH:MM' 形式
        $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i'));
        $response->assertSee(\Carbon\Carbon::parse($attendance->clock_out)->format('H:i'));

        $response->assertSee($request->reason);
       

        $response->assertStatus(200);
    }

    public function test_修正申請の承認処理が正しく行われる() 
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        // Attendance を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        // Request を作成（attendance_id に紐付け）
        $request = Request::factory()->create([
            'attendance_id' => $attendance->id,
            'requested_clock_in'  => Carbon::createFromTime(9, 0, 0),
            'requested_clock_out' => Carbon::createFromTime(18, 0, 0),
            'status'        => 'applied',
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)
            ->patch(route('admin.stamp_request.approve', $request->id));

        // リダイレクトまたはステータス確認
        $response->assertStatus(302); // リダイレクトの場合

        // DB上で承認済みに更新されていることを確認
        $this->assertDatabaseHas('requests', [
            'id'     => $request->id,
            'status' => 'approved',
        ]);
    }
}
