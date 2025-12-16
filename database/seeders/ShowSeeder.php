<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShowSeeder extends Seeder
{
    public function run(): void
    {
        // Danh sách movie_id và cinema_id (giả sử đã có từ seeder trước)
        $movieIds = [1, 2, 3, 4, 5]; // Thay bằng movie_id thực tế nếu cần
        $cinemas = [
            1 => ['R101', 'R102', 'R103', 'R104', 'R105'], // cinema_id 1
            2 => ['R201', 'R202', 'R203', 'R204'],           // cinema_id 2
        ];

        $seats = [
            'R101' => 80, 'R102' => 60, 'R103' => 90, 'R104' => 80, 'R105' => 120,
            'R201' => 80, 'R202' => 100, 'R203' => 120, 'R204' => 120,
        ];

        $startDate = Carbon::parse('2025-12-17');
        $endDate = Carbon::parse('2025-12-26');

        $shows = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            foreach ($cinemas as $cinemaId => $rooms) {
                foreach ($rooms as $room) {
                    // Mỗi phòng có khoảng 4-6 suất chiếu/ngày
                    $numShows = rand(4, 6);

                    for ($i = 0; $i < $numShows; $i++) {
                        $movieId = $movieIds[array_rand($movieIds)];

                        // Giờ bắt đầu ngẫu nhiên từ 09:00 đến 21:30
                        $hour = rand(9, 21);
                        $minute = [0, 30][rand(0, 1)];
                        $startTime = sprintf('%02d:%02d:00', $hour, $minute);

                        // Thời lượng phim ngẫu nhiên 120-180 phút
                        $duration = rand(120, 180);
                        $endTime = Carbon::createFromFormat('H:i:s', $startTime)
                            ->addMinutes($duration)
                            ->format('H:i:s');

                        $shows[] = [
                            'cinema_id'       => $cinemaId,
                            'movie_id'        => $movieId,
                            'room_code'       => $room,
                            'show_date'       => $date->toDateString(),
                            'start_time'      => $startTime,
                            'end_time'        => $endTime,
                            'remaining_seats' => $seats[$room],
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ];
                    }
                }
            }
        }

        // Insert batch để nhanh và tránh lỗi
        DB::table('shows')->insert($shows);
    }
}