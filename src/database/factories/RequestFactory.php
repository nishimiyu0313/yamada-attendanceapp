<?php

namespace Database\Factories;

use App\Models\Request;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestFactory extends Factory
{
    protected $model = Request::class;

    public function definition(): array
    {
        return [
            'attendance_id' => 1, // これは必須FKがある場合。nullableなら不要
            'requested_clock_in' => '09:00:00',
            'requested_clock_out' => '18:00:00',
            'reason' => '修正申請テスト',
            'applied_date' => $this->faker->date(),
            'status' => 'pending',
            'approver_id' => null,
        ];
    }
}
