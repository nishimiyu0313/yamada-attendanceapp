<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Request;
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
                $workDate = now()->subDays($i);

                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => $workDate->format('Y-m-d'),
                ]);

                if ($i % 5 === 0) {
                    Request::factory()->create([
                        'attendance_id' => $attendance->id,
                        'status' => 'applied',
                        'requested_clock_in' => \Carbon\Carbon::parse($attendance->clock_in)->addHour(),
                        'requested_clock_out' => \Carbon\Carbon::parse($attendance->clock_out)->addHour(),
                    ]);
                }
            }
        }
    }
}
