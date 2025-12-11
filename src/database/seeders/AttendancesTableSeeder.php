<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            for ($i = 0; $i < 20; $i++) {
                $workDate = now()->subDays($i); // 今日から遡る日付

                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => $workDate->format('Y-m-d'),
                ]);

                \App\Models\BreakTime::factory()
                    ->count(rand(1, 2))
                    ->create([
                        'attendance_id' => $attendance->id,
                    ]);
            }
        }
    }
}
