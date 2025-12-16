<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class UserdetailgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);

        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => '2025-12-13',
            'clock_in'  => '2025-12-13 09:00:00',
            'clock_out' => '2025-12-13 18:00:00',
            'status'    => 'finished',
        ]);


        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $attendance->id]));


        $response->assertSee($user->name);


        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));

        $response->assertStatus(200);
    }

    public function test_勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);

        $attendance = \App\Models\Attendance::create([
            'user_id'   => $user->id,
            'work_date' => \Carbon\Carbon::parse('2025-12-13'),
            'clock_in'  => \Carbon\Carbon::parse('2025-12-13 09:00:00'),
            'clock_out' => \Carbon\Carbon::parse('2025-12-13 18:00:00'),
            'status'    => 'finished',
        ]);

        /** @var \App\Models\User $user */

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', $attendance->id));


        $formattedDate = \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日');

        $response->assertSeeText($formattedDate);

        $response->assertStatus(200);
    }

    public function test_「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
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

    public function test_「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create(['name' => 'テストユーザー']);


        $attendance = \App\Models\Attendance::create([
            'user_id'   => $user->id,
            'work_date' => '2025-12-13',
            'clock_in'  => '2025-12-13 09:00:00',
            'clock_out' => '2025-12-13 18:00:00',
            'status'    => 'finished',
        ]);

        $break1 = \App\Models\BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => '2025-12-13 12:00:00',
            'break_end'     => '2025-12-13 12:30:00',
        ]);

        $break2 = \App\Models\BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => '2025-12-13 15:00:00',
            'break_end'     => '2025-12-13 15:15:00',
        ]);

        /** @var \App\Models\User $user */

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $attendance->id]));

        $break1Start = \Carbon\Carbon::parse($break1->break_start)->format('H:i');
        $break1End   = \Carbon\Carbon::parse($break1->break_end)->format('H:i');
        $break2Start = \Carbon\Carbon::parse($break2->break_start)->format('H:i');
        $break2End   = \Carbon\Carbon::parse($break2->break_end)->format('H:i');


        $response->assertSee($break1Start);
        $response->assertSee($break1End);
        $response->assertSee($break2Start);
        $response->assertSee($break2End);

        $response->assertStatus(200);
    }
}
