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
            'clock_out' => '09:00',
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
                        'start' => '19:00',
                        'end' => '19:30',
                    ]
                ],
            ]
        );

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
                    'end' => '19:00',
                ]
            ],
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();


        $attendance = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::yesterday(),
            'clock_in'  => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
            'status'    => Attendance::STATUS_WORKING,
        ]);


        $response = $this->actingAs($user)->post(
            route('attendance.detailstore', ['id' => $attendance->id]),
            [
                'clock_in'  => '09:00',
                'clock_out' => '18:00',
                'reason'    => '',
                'breaks'    => [
                    ['id' => 1, 'start' => '12:00', 'end' => '12:30'],
                ],
            ]
        );


        $response->assertSessionHasErrors(['reason']);

        $this->assertEquals(
            '備考を入力してください',
            session('errors')->get('reason')[0]
        );
    }

    public function test_修正申請処理が実行される()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);


        $attendance = \App\Models\Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2025-12-13',
            'clock_in'  => '2025-12-13 09:00:00',
            'clock_out' => '2025-12-13 18:00:00',
            'status'    => 'finished',
        ]);

        /** @var \App\Models\User $user */

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $attendance->id]));


        $clockInFormatted  = \Carbon\Carbon::parse($attendance->clock_in)->format('H:i');
        $clockOutFormatted = \Carbon\Carbon::parse($attendance->clock_out)->format('H:i');


        $response->assertSee($clockInFormatted);
        $response->assertSee($clockOutFormatted);


        $response->assertStatus(200);
    }

    public function test_「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);

        $admin = User::factory()->create(['role' => 'admin']);

        $attendance = \App\Models\Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2025-12-13',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'status'    => 'finished',
        ]);

        $request = \App\Models\Request::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'reason'        => '修正理由',
            'status'        => 'applied',
            'requested_clock_in' => now(),
            'requested_clock_out' => now()->addHours(8),
            'applied_date' => now(),
            'approver_id' => $admin->id,
        ]);


        /** @var \App\Models\User $user */

        $response = $this->actingAs($user)
            ->get(route('attendance.request', ['status' => 'applied']));


        $response->assertSee('修正理由');


        $response->assertSee($user->name);

        $response->assertStatus(200);
    }

    public function test_「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        $user = User::factory()->create(['name' => 'ユーザー1']);


        $admin = User::factory()->create(['role' => 'admin']);


        $attendance = \App\Models\Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2025-12-13',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'status'    => 'finished',
        ]);

        $request = \App\Models\Request::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'reason'        => '修正理由',
            'status'        => 'approved',
            'requested_clock_in' => now(),
            'requested_clock_out' => now()->addHours(8),
            'applied_date' => now(),
            'approver_id' => $admin->id,
        ]);


        /** @var \App\Models\User $user */

        $response = $this->actingAs($user)
            ->get(route('attendance.request', ['status' => 'approved']));

        $response->assertSee('修正理由');


        $response->assertSee($user->name);

        $response->assertStatus(200);
    }

    public function test_各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);

        $attendance = \App\Models\Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2025-12-13',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'status'    => 'finished',
        ]);

        $request = \App\Models\Request::create([
            'attendance_id' => $attendance->id,
            'reason'        => '修正理由',
            'status'        => 'approved',
            'requested_clock_in' => now(),
            'requested_clock_out' => now()->addHours(8),
            'applied_date' => now(),
            'approver_id' => null,
        ]);

        /** @var \App\Models\User $user */
        $indexResponse = $this->actingAs($user)
            ->get(route('attendance.request'));

        $indexResponse->assertSee('詳細');


        $detailResponse = $this->actingAs($user)
            ->get(route('attendance.detailrequest', $request->id));
        /** @var \Illuminate\Testing\TestResponse $detailResponse */


        $detailResponse->assertStatus(200);
        $detailResponse->assertViewIs('user.detail');
        $detailResponse->assertViewHas('attendance');
    }
}
