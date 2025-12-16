<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        $workDate = Carbon::today();
        $clockIn = $workDate->copy()->setTime(9, 0);  
        $clockOut = $workDate->copy()->setTime(18, 0); 

        return [
            'user_id'   => \App\Models\User::factory(),
            'work_date' => $workDate->format('Y-m-d'),
            'clock_in'  => $clockIn,
            'clock_out' => $clockOut,
            'status'    => 'finished',
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Attendance $attendance) {
            $breakTimes = [
                ['12:00', '12:30'],
                ['15:00', '15:15'],
            ];

            
                foreach ($breakTimes as $bt) {
                    BreakTime::factory()->create([
                        'attendance_id' => $attendance->id,
                        'break_start'   => Carbon::today()->copy()->setTime((int)explode(':', $bt[0])[0], (int)explode(':', $bt[0])[1]),
                        'break_end'     => Carbon::today()->copy()->setTime((int)explode(':', $bt[1])[0], (int)explode(':', $bt[1])[1]),
                    ]);
                }
        });
    }
}
