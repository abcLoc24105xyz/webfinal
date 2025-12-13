<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CinemaSeeder extends Seeder {
    public function run(): void {
        DB::table('cinemas')->insert([
            [
                'cinema_id' => 1,
                'cinema_name' => 'Rạp phim Vincom Center',
                'address' => 'Tầng 5, Vincom Center, Hà Nội',
                'phone' => '02499998888',
                'status' => 1,
            ],
            [
                'cinema_id' => 2,
                'cinema_name' => 'Rạp phim Landmark 81',
                'address' => 'Vinhomes Landmark 81, TP.HCM',
                'phone' => '02899997777',
                'status' => 1,
            ],
        ]);
    }
}