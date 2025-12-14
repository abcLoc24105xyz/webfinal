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
            SeatSeeder::class,
            MovieSeeder::class,
            ShowSeeder::class,
            UserSeeder::class,
            AdminSeeder::class,
            PromoCodeSeeder::class,
        ]);
    }
}