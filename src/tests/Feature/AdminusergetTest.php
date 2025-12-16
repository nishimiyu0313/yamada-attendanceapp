<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;

class AdminusergetTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $admin = User::factory()->create(['role' => 'admin']);

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
        $response = $this->actingAs($admin)->get('/admin/staff/list');


        $response->assertSee('ユーザー1');
        $response->assertSee('user1@example.com');
        $response->assertSee('ユーザー2');
        $response->assertSee('user2@example.com');

        $response->assertDontSee($admin->email);

        $response->assertStatus(200);
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {

        $admin = User::factory()->create(['role' => 'admin']);


        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::factory()->for($user)->create([
            'work_date' => '2025-12-13',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'status'    => 'finished',
        ]);

        /** @var \App\Models\User $admin */
        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', ['id' => $user->id]));

        $response->assertSee($user->name);
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));


        $response->assertStatus(200);
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);


        $this->actingAs($admin);

        $prevDate = today()->subMonth();

        $response = $this->get(route('admin.attendance.staff', [
            'id' => $admin->id,
            'work_date' => $prevDate->format('Y-m-d')
        ]));

        $expectedMonth = $prevDate->format('Y/m');

        $response->assertStatus(200);
        $response->assertSee($expectedMonth);
    }

    public function test_「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        $prevDate = today()->addMonth();

        $response = $this->get(route('admin.attendance.staff', [
            'id' => $admin->id,
            'work_date' => $prevDate->format('Y-m-d')
        ]));

        $expectedMonth = $prevDate->format('Y/m');

        $response->assertStatus(200);
        $response->assertSee($expectedMonth);
    }


    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $admin->id,
            'work_date' => today(),

        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.staff', ['id' => $admin->id]));

        $response->assertStatus(200);
    }
}
