<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;

class AdmindetailupdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        /** @var \App\Models\User $admin */

        $admin = User::factory()->create(['role' => 'admin']);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => '2025-12-13',
            'clock_in'  => Carbon::create(2025, 12, 13, 9, 0),
            'clock_out' => Carbon::create(2025, 12, 13, 18, 0),
            'status'    => 'finished',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.detail', $attendance->id));

        $response->assertStatus(200);

        $response->assertViewIs('admin.detail');


        $response->assertSee($user->name);

        $response->assertSee(\Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日'));

        $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i'));
        $response->assertSee(\Carbon\Carbon::parse($attendance->clock_out)->format('H:i'));


        $response->assertSee($attendance->reason);
    }

    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        /** @var \App\Models\User $admin */

        $admin = User::factory()->create(['role' => 'admin']);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-12-13',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.request', $attendance->id), [
                'clock_in'  => '19:00',
                'clock_out' => '18:00',
                'reason'    => 'テスト用備考',
            ]);


        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間が不適切な値です',
        ]);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();


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

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.request', $attendance->id), [

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
            ]);


        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }


    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        /** @var \App\Models\User $admin */

        $admin = User::factory()->create(['role' => 'admin']);

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

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.request', $attendance->id), [
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
        /** @var \App\Models\User $admin */

        $admin = User::factory()->create(['role' => 'admin']);

        $attendance = Attendance::create([
            'user_id'   => $admin->id,
            'work_date' => Carbon::yesterday(),
            'clock_in'  => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.attendance.request', ['id' => $attendance->id]),
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
}
