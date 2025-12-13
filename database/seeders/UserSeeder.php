<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    public function run(): void {
        DB::table('users')->insert([
            [
                'user_id' => 1,
                'full_name' => 'User 1',
                'email' => 'user1@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0987654321',
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'user_id' => 2,
                'full_name' => 'User 2',
                'email' => 'user2@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0987654322',
                'status' => 1,
                'created_at' => now(),
            ],
        ]);
    }
}