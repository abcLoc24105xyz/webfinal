<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder {
    public function run(): void {
        $rooms = [
            // Cinema 1 - Vincom
            ['room_code' => 'R101', 'cinema_id' => 1, 'room_name' => 'Phòng 1', 'room_type' => 1, 'total_seats' => 80],
            ['room_code' => 'R102', 'cinema_id' => 1, 'room_name' => 'Phòng 2', 'room_type' => 1, 'total_seats' => 60],
            ['room_code' => 'R103', 'cinema_id' => 1, 'room_name' => 'Phòng 3', 'room_type' => 1, 'total_seats' => 90],
            ['room_code' => 'R104', 'cinema_id' => 1, 'room_name' => 'Phòng 4', 'room_type' => 1, 'total_seats' => 80],
            ['room_code' => 'R105', 'cinema_id' => 1, 'room_name' => 'Phòng 5', 'room_type' => 1, 'total_seats' => 120],
            // Cinema 2 - Landmark 81
            ['room_code' => 'R201', 'cinema_id' => 2, 'room_name' => 'Hall A', 'room_type' => 1, 'total_seats' => 80],
            ['room_code' => 'R202', 'cinema_id' => 2, 'room_name' => 'Hall B', 'room_type' => 1, 'total_seats' => 100],
            ['room_code' => 'R203', 'cinema_id' => 2, 'room_name' => 'Hall C', 'room_type' => 1, 'total_seats' => 120],
            ['room_code' => 'R204', 'cinema_id' => 2, 'room_name' => 'Hall D', 'room_type' => 1, 'total_seats' => 120],
        ];
        
        DB::table('rooms')->insert($rooms);
    }
}
