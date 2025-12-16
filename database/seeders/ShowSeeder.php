<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShowSeeder extends Seeder
{
    public function run(): void
    {
        // XÓA DỮ LIỆU CŨ AN TOÀN (không dùng truncate vì bị ràng buộc foreign key)
        DB::table('shows')->delete();

        $shows = [];

        // Danh sách movie_id có sẵn
        $movieIds = [1, 2, 3, 4, 5];

        // Danh sách cinema_id và phòng tương ứng
        $cinemaRooms = [
            1 => ['R101', 'R102', 'R103', 'R104', 'R105'],
            2 => ['R201', 'R202', 'R203', 'R204'],
        ];

        // Các khung giờ chiếu cố định
        $startTimes = ['09:00:00', '11:30:00', '14:00:00', '16:30:00', '19:00:00', '21:30:00'];

        // Tạo suất chiếu cho 10 ngày kể từ hôm nay
        $startDate = Carbon::today();

        $globalCounter = 1; // Đếm toàn cục để xxx tăng liên tục, đảm bảo unique

        for ($day = 0; $day < 10; $day++) {
            $showDate = $startDate->copy()->addDays($day)->format('Y-m-d');
            $dateFormatted = $startDate->copy()->addDays($day)->format('Ymd'); // yyyyMMdd

            foreach ($cinemaRooms as $cinemaId => $rooms) {
                foreach ($rooms as $room) {
                    // Mỗi phòng mỗi ngày có 3-5 suất chiếu ngẫu nhiên
                    $numShows = rand(3, 5);
                    $usedTimes = [];

                    for ($i = 0; $i < $numShows; $i++) {
                        // Chọn phim ngẫu nhiên
                        $movieId = $movieIds[array_rand($movieIds)];

                        // Chọn giờ bắt đầu ngẫu nhiên, tránh trùng trong phòng
                        do {
                            $startTime = $startTimes[array_rand($startTimes)];
                        } while (in_array($startTime, $usedTimes));
                        $usedTimes[] = $startTime;

                        // Tính giờ kết thúc dựa trên thời lượng phim
                        $movieDuration = DB::table('movies')
                            ->where('movie_id', $movieId)
                            ->value('duration') ?? 120;

                        $startTimeObj = Carbon::createFromFormat('H:i:s', $startTime);
                        $endTimeObj = $startTimeObj->copy()->addMinutes($movieDuration);
                        $endTime = $endTimeObj->format('H:i:s');

                        // Lấy số ghế từ phòng
                        $totalSeats = DB::table('rooms')
                            ->where('room_code', $room)
                            ->value('total_seats') ?? 80;

                        // Tạo show_id dạng SHOWyyyyMMddxxx (xxx tăng toàn cục)
                        $sequence = str_pad($globalCounter, 3, '0', STR_PAD_LEFT);
                        $showId = 'SHOW' . $dateFormatted . $sequence;

                        $shows[] = [
                            'show_id'         => $showId,
                            'movie_id'        => $movieId,
                            'cinema_id'       => $cinemaId,
                            'room_code'       => $room,
                            'show_date'       => $showDate,
                            'start_time'      => $startTime,
                            'end_time'        => $endTime,
                            'remaining_seats' => $totalSeats,
                        ];

                        $globalCounter++;
                    }
                }
            }
        }

        // Insert batch
        DB::table('shows')->insert($shows);
    }
}