<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShowSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ an toàn
        DB::table('shows')->delete();

        $shows = [];

        // Danh sách movie_id có sẵn
        $movieIds = [1, 2, 3, 4, 5];

        // Danh sách cinema_id và phòng tương ứng
        $cinemaRooms = [
            1 => ['R101', 'R102', 'R103', 'R104', 'R105'],
            2 => ['R201', 'R202', 'R203', 'R204'],
        ];

        // Thêm nhiều khung giờ chiếu hơn (từ 8h sáng đến 23h30, mỗi 30 phút hoặc linh hoạt)
        $startTimes = [
            '08:00:00', '08:30:00',
            '09:00:00', '09:30:00',
            '10:00:00', '10:30:00',
            '11:00:00', '11:30:00',
            '12:00:00', '12:30:00',
            '13:00:00', '13:30:00',
            '14:00:00', '14:30:00',
            '15:00:00', '15:30:00',
            '16:00:00', '16:30:00',
            '17:00:00', '17:30:00',
            '18:00:00', '18:30:00',
            '19:00:00', '19:30:00',
            '20:00:00', '20:30:00',
            '21:00:00', '21:30:00',
            '22:00:00', '22:30:00',
            '23:00:00', '23:30:00',
        ];

        // Tạo suất chiếu cho 10 ngày kể từ hôm nay
        $startDate = Carbon::today();

        $globalCounter = 1; // Đếm toàn cục để xxx tăng liên tục

        for ($day = 0; $day < 10; $day++) {
            $showDate = $startDate->copy()->addDays($day)->format('Y-m-d');
            $dateFormatted = $startDate->copy()->addDays($day)->format('Ymd'); // yyyyMMdd

            foreach ($cinemaRooms as $cinemaId => $rooms) {
                foreach ($rooms as $room) {
                    // Mỗi phòng mỗi ngày có 6-10 suất chiếu ngẫu nhiên (nhiều hơn)
                    $numShows = rand(6, 10);
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

                        // Tạo show_id dạng SHOWyyyyMMddxxx
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