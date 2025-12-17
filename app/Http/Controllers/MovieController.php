<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Show;
use App\Models\Cinema;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Hiển thị trang chi tiết phim + hỗ trợ lọc theo rạp
     */
    public function show(Request $request, $slug)
    {
        $movie = Movie::where('slug', $slug)->with('category')->firstOrFail();

        Carbon::setLocale('vi');
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $today = Carbon::today('Asia/Ho_Chi_Minh');

        // Xử lý suất chiếu sớm
        $earlyPremiereDate = null;
        $hasEarlyPremiere = false;

        if ($movie->early_premiere_date) {
            $earlyDate = Carbon::parse($movie->early_premiere_date)->startOfDay();
            if ($earlyDate->gte($today)) {
                $earlyShowExists = Show::where('movie_id', $movie->movie_id)
                    ->whereDate('show_date', $earlyDate)
                    ->where('remaining_seats', '>', 0)
                    ->whereRaw("CONCAT(show_date, ' ', start_time) > ?", [$now])
                    ->exists();

                if ($earlyShowExists) {
                    $earlyPremiereDate = $earlyDate;
                    $hasEarlyPremiere = true;
                }
            }
        }

        // Tính toán ngày kết thúc để lấy dữ liệu
        $endDate = $today->copy()->addDays(7);
        if ($hasEarlyPremiere && $earlyPremiereDate->gt($endDate)) {
            $endDate = $earlyPremiereDate;
        }

        // Lấy danh sách tất cả rạp và rạp có suất chiếu
        $allCinemas = Cinema::orderBy('cinema_name')->get();
        
        $selectedCinemaId = $request->filled('cinema') ? (int)$request->cinema : null;

        $availableCinemas = Cinema::whereHas('shows', function ($q) use ($movie, $today, $now, $endDate) {
            $q->where('movie_id', $movie->movie_id)
              ->where('show_date', '>=', $today)
              ->where('show_date', '<=', $endDate)
              ->where('remaining_seats', '>', 0)
              ->whereRaw("CONCAT(show_date, ' ', start_time) > ?", [$now]);
        })->orderBy('cinema_name')->get();

        // Lấy danh sách ngày có suất chiếu
        $availableDates = Show::where('movie_id', $movie->movie_id)
            ->where('show_date', '>=', $today)
            ->where('show_date', '<=', $endDate)
            ->where('remaining_seats', '>', 0)
            ->whereRaw("CONCAT(show_date, ' ', start_time) > ?", [$now])
            ->when($selectedCinemaId, fn($q) => $q->where('cinema_id', $selectedCinemaId))
            ->distinct()
            ->orderBy('show_date')
<<<<<<< HEAD
            ->pluck('show_date'); // Carbon object

        // Xử lý suất chiếu sớm
        if ($hasEarlyPremiere) {
            $earlyDateStr = $earlyPremiereDate->toDateString();

            $availableDates = $availableDates->map(fn($date) => $date instanceof Carbon ? $date->toDateString() : (string)$date);

=======
            ->pluck('show_date'); // ← Đây là Carbon object

        // === SỬA TỪ ĐÂY ===
        if ($hasEarlyPremiere) {
            $earlyDateStr = $earlyPremiereDate->toDateString();

            // Chuẩn hóa tất cả về string để so sánh
            $availableDates = $availableDates->map(fn($date) => $date instanceof \Carbon\Carbon ? $date->toDateString() : (string)$date);

            // Chỉ thêm nếu chưa có
>>>>>>> 3a03ec3 (final)
            if (!$availableDates->contains($earlyDateStr)) {
                $availableDates->prepend($earlyDateStr);
            }

<<<<<<< HEAD
=======
            // Loại trùng và sắp xếp lại
>>>>>>> 3a03ec3 (final)
            $availableDates = $availableDates->unique()->values();
        }

        // Xử lý suất chiếu sớm theo rạp được chọn
<<<<<<< HEAD
        if ($hasEarlyPremiere && $selectedCinemaId) {
            $hasEarlyShowInCinema = Show::where('movie_id', $movie->movie_id)
                ->where('cinema_id', $selectedCinemaId)
                ->whereDate('show_date', $earlyPremiereDate)
                ->where('remaining_seats', '>', 0)
                ->whereRaw("CONCAT(show_date, ' ', start_time) > ?", [$now])
                ->exists();
            
            if (!$hasEarlyShowInCinema) {
                $hasEarlyPremiere = false;
=======
        if ($hasEarlyPremiere) {
            if ($selectedCinemaId) {
                $hasEarlyShowInCinema = Show::where('movie_id', $movie->movie_id)
                    ->where('cinema_id', $selectedCinemaId)
                    ->whereDate('show_date', $earlyPremiereDate)
                    ->where('remaining_seats', '>', 0)
                    ->whereRaw("CONCAT(show_date, ' ', start_time) > ?", [$now])
                    ->exists();
                
                if (!$hasEarlyShowInCinema) {
                    $hasEarlyPremiere = false;
                }
            }

            // Thêm ngày suất sớm vào danh sách
            if ($hasEarlyPremiere) {
                $availableDates = $availableDates->filter(
                    fn($date) => $date !== $earlyPremiereDate->toDateString()
                )->values();
                $availableDates->prepend($earlyPremiereDate->toDateString());
>>>>>>> 3a03ec3 (final)
            }
        }

        // Chọn ngày hiển thị mặc định
        $selectedDate = $availableDates->isNotEmpty()
            ? Carbon::parse($availableDates->first())
            : $today;

        if ($request->filled('date')) {
            $reqDate = Carbon::parse($request->date)->startOfDay();
            if ($availableDates->contains($reqDate->toDateString())) {
                $selectedDate = $reqDate;
            }
        }

        // Lấy suất chiếu theo ngày + rạp
        $shows = Show::with(['cinema', 'room'])
            ->where('movie_id', $movie->movie_id)
            ->whereDate('show_date', $selectedDate)
            ->where('remaining_seats', '>', 0)
            ->whereRaw("CONCAT(show_date, ' ', start_time) > ?", [$now])
            ->when($selectedCinemaId, fn($q) => $q->where('cinema_id', $selectedCinemaId))
            ->orderBy('start_time')
            ->get()
            ->groupBy('cinema_id');

        return view('movie.detail', compact(
            'movie',
            'availableDates',
            'selectedDate',
            'shows',
            'allCinemas',
            'availableCinemas',
            'selectedCinemaId',
            'hasEarlyPremiere',
            'earlyPremiereDate'
        ));
    }

    /**
     * Load lại suất chiếu khi đổi ngày hoặc rạp (AJAX)
     */
    public function loadShows($slug, Request $request)
    {
        $movie = Movie::where('slug', $slug)->firstOrFail();

        $date = $request->filled('date')
<<<<<<< HEAD
            ? Carbon::parse($request->date)->startOfDay()
=======
            ? Carbon::parse($request->date)
>>>>>>> 3a03ec3 (final)
            : Carbon::today('Asia/Ho_Chi_Minh');

        $cinemaId = $request->filled('cinema') ? (int)$request->cinema : null;
        $now = Carbon::now('Asia/Ho_Chi_Minh');

<<<<<<< HEAD
        // Lấy danh sách rạp có suất chiếu (cần cho tiêu đề trong partial)
        $availableCinemas = Cinema::whereHas('shows', function ($q) use ($movie, $date, $now, $cinemaId) {
            $q->where('movie_id', $movie->movie_id)
              ->whereDate('show_date', $date)
              ->where('remaining_seats', '>', 0)
              ->whereRaw("CONCAT(show_date, ' ', start_time) > ?", [$now])
              ->when($cinemaId, fn($qq) => $qq->where('cinema_id', $cinemaId));
        })->orderBy('cinema_name')->get();

=======
>>>>>>> 3a03ec3 (final)
        $shows = Show::with(['cinema', 'room'])
            ->where('movie_id', $movie->movie_id)
            ->whereDate('show_date', $date)
            ->where('remaining_seats', '>', 0)
            ->whereRaw("CONCAT(show_date, ' ', start_time) > ?", [$now])
            ->when($cinemaId, fn($q) => $q->where('cinema_id', $cinemaId))
            ->orderBy('start_time')
            ->get()
            ->groupBy('cinema_id');

<<<<<<< HEAD
        return view('movie.partials.showtimes', compact('shows', 'availableCinemas'));
=======
        return view('movie.partials.showtimes', compact('shows'));
>>>>>>> 3a03ec3 (final)
    }
}