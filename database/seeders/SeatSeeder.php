<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeatSeeder extends Seeder {
    public function run(): void {
        $seats = [];
        $seatId = 1;
        
        // Room R101 (Cinema 1) - 80 seats
        $seats = array_merge($seats, $this->generateSeatsForRoom('R101', 8, 10, $seatId));
        
        // Room R102 (Cinema 1) - 60 seats
        $seats = array_merge($seats, $this->generateSeatsForRoom('R102', 6, 10, $seatId));
        
        // Room R103 (Cinema 1) - 90 seats
        $seats = array_merge($seats, $this->generateSeatsForRoom('R103', 9, 10, $seatId));
        
        // Room R104 (Cinema 1) - 80 seats
        $seats = array_merge($seats, $this->generateSeatsForRoom('R104', 8, 10, $seatId));
        
        // Room R105 (Cinema 1) - 120 seats
        $seats = array_merge($seats, $this->generateSeatsForRoom('R105', 12, 10, $seatId));
        
        // Room R201 (Cinema 2) - 80 seats
        $seats = array_merge($seats, $this->generateSeatsForRoom('R201', 8, 10, $seatId));
        
        // Room R202 (Cinema 2) - 100 seats
        $seats = array_merge($seats, $this->generateSeatsForRoom('R202', 10, 10, $seatId));
        
        // Room R203 (Cinema 2) - 120 seats
        $seats = array_merge($seats, $this->generateSeatsForRoom('R203', 12, 10, $seatId));
        
        // Room R204 (Cinema 2) - 120 seats
        $seats = array_merge($seats, $this->generateSeatsForRoom('R204', 12, 10, $seatId));
        
        DB::table('seats')->insert($seats);
    }
    
    private function generateSeatsForRoom($roomCode, $rows, $cols, &$seatId): array {
        $seats = [];
        $rows_letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        
        foreach (array_slice($rows_letters, 0, $rows) as $row) {
            for ($col = 1; $col <= $cols; $col++) {
                // Determine seat type and price
                $seatType = 1; // Regular
                $price = 50000;
                
                // VIP seats (rows in middle)
                if (in_array($row, ['E', 'F', 'G']) || ($row === 'D' && $roomCode !== 'R102' && $roomCode !== 'R202')) {
                    $seatType = 2;
                    $price = 80000;
                }
                
                // Couple seats (last row)
                if ($row === 'H' || $row === 'I' || $row === 'J' || $row === 'K' || $row === 'L') {
                    $seatType = 3;
                    $price = 60000;
                }
                
                $seats[] = [
                    'seat_id' => $seatId++,
                    'room_code' => $roomCode,
                    'seat_num' => $row . $col,
                    'seat_type' => $seatType,
                    'default_price' => $price,
                ];
            }
        }
        
        return $seats;
    }
}