<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;

class BreakTimeFactory extends Factory
{
    public function definition()
    {
        $breakStart = $this->faker->dateTimeBetween('-8 hours', 'now');
        $breakEnd = (clone $breakStart)->modify('+1 hour');

        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ];
    }
}
