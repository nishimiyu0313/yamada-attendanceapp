<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (User::where('email', 'admin@example.com')->exists()) {
            return;
        }

        User::create([
            'name' => 'admin taro',
            'email' => 'admin@email',
            'password' => Hash::make('adminpassword'),
            'role' => 'admin',
        ]);
    }
}
