<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class UserattendancelistTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分が行った勤怠情報が全て表示されている()
    {
        /** @var User $user */
        $user = User::factory()->create();


        $attendances = Attendance::factory()->count(3)->sequence(
            ['work_date' => Carbon::today()->addDays(1)],
            ['work_date' => Carbon::today()->addDays(2)],
            ['work_date' => Carbon::today()->addDays(3)]
        )->create([
            'user_id'   => $user->id,
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);


        $otherUser = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => Carbon::today()->addDays(1),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);


        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $dateString = Carbon::parse($attendance->work_date)->format('n/j');
            $clockIn    = Carbon::parse($attendance->clock_in)->format('H:i');
            $clockOut   = Carbon::parse($attendance->clock_out)->format('H:i');

            $response->assertSee($dateString);
            $response->assertSee($clockIn);
            $response->assertSee($clockOut);
        }
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

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


        $this->actingAs($user);


        $prevDate = today()->subMonth();


        $response = $this->get(route('attendance.list', [
            'work_date' => $prevDate->format('Y-m-d')
        ]));

        $expectedMonth = $prevDate->format('Y/m');

        $response->assertStatus(200);
        $response->assertSee($expectedMonth);
    }

    public function test_「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\User $user */
        $user = \App\Models\User::factory()->create();


        $this->actingAs($user);


        $prevDate = today()->addMonth();

        $response = $this->get(route('attendance.list', [
            'work_date' => $prevDate->format('Y-m-d')
        ]));


        $expectedMonth = $prevDate->format('Y/m');

        $response->assertStatus(200);
        $response->assertSee($expectedMonth);
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        /** @var \App\Models\User $user */
        $user = \App\Models\User::factory()->create();

        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
        ]);


        $response = $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $attendance->id]));


        $response->assertStatus(200);
    }
}
