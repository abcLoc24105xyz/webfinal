<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder {
    public function run(): void {
        DB::table('admins')->insert([
            [
                'admin_id' => 1,
                'name' => 'Super Admin',
                'email' => 'admin@cinema.vn',
                'password' => Hash::make('password123'),
                'phone' => '0909123456',
                'is_super' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'admin_id' => 2,
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0123456789',
                'is_super' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
