<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShowSeeder extends Seeder {
    public function run(): void {
        $shows = [];
        $showId = 1;
        
        // Movies để chiếu: 1,2,3,4,5
        $movies = [1, 2, 3, 4, 5];
        $cinemas = [1, 2];
        
        // Room mapping
        $cinemaRooms = [
            1 => ['R101', 'R102', 'R103', 'R104', 'R105'],
            2 => ['R201', 'R202', 'R203', 'R204']
        ];
        
        $startTimes = ['09:00:00', '11:30:00', '14:00:00', '16:30:00', '19:00:00', '21:30:00'];
        
        // Tạo shows cho 10 ngày từ hôm nay
        for ($day = 0; $day < 10; $day++) {
            $showDate = now()->addDays($day)->toDateString();
            
            foreach ($cinemas as $cinema) {
                foreach ($cinemaRooms[$cinema] as $roomCode) {
                    // Random 2-4 shows per room per day
                    $numShows = rand(2, 4);
                    $usedTimes = [];
                    
                    for ($i = 0; $i < $numShows; $i++) {
                        $movie = $movies[array_rand($movies)];
                        $startTime = $startTimes[array_rand($startTimes)];
                        
                        // Tránh duplicate time
                        while (in_array($startTime, $usedTimes)) {
                            $startTime = $startTimes[array_rand($startTimes)];
                        }
                        $usedTimes[] = $startTime;
                        
                        // Lấy duration từ movie
                        $movieDuration = DB::table('movies')
                            ->where('movie_id', $movie)
                            ->value('duration');
                        
                        $startTimeObj = \DateTime::createFromFormat('H:i:s', $startTime);
                        $endTimeObj = clone $startTimeObj;
                        $endTimeObj->add(new \DateInterval('PT' . $movieDuration . 'M'));
                        $endTime = $endTimeObj->format('H:i:s');
                        
                        // Lấy total_seats từ room
                        $totalSeats = DB::table('rooms')
                            ->where('room_code', $roomCode)
                            ->value('total_seats');
                        
                        $shows[] = [
                            'show_id' => bin2hex(str_pad($showId, 16, '0', STR_PAD_LEFT)),
                            'movie_id' => $movie,
                            'cinema_id' => $cinema,
                            'room_code' => $roomCode,
                            'show_date' => $showDate,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'remaining_seats' => $totalSeats,
                        ];
                        
                        $showId++;
                    }
                }
            }
        }
        
        DB::table('shows')->insert($shows);
    }
}