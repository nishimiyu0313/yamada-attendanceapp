<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        $clockIn = $this->faker->dateTimeBetween('-1 month', 'now');
        $clockOut = (clone $clockIn)->modify('+8 hours'); // 勤務8時間

        return [
            'work_date' => $clockIn->format('Y-m-d'),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => 'finished',
        ];
    }
}
