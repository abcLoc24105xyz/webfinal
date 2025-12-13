<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        $this->call([
            CategorySeeder::class,
            CinemaSeeder::class,
            ComboSeeder::class,
            RoomSeeder::class,
            SeatSeeder::class,        // ✅ Thêm dòng này
            MovieSeeder::class,       // ✅ Thêm dòng này
            AdminSeeder::class,
            UserSeeder::class,
            PromoCodeSeeder::class,
        ]);
    }
}