{{-- resources/views/movie/detail.blade.php --}}
@extends('layouts.app')
@section('title', $movie->title)
@section('content')

@php
    $earlyDate = $earlyPremiereDate ?? null;
    $releaseDate = $movie->release_date ? \Carbon\Carbon::parse($movie->release_date) : null;
    $isReleased = $releaseDate && $releaseDate->lte(now());

    // Kiểm tra có suất chiếu nào trong tương lai không
    $hasUpcomingShows = $availableDates->contains(function ($date) {
        return \Carbon\Carbon::parse($date)->gte(now()->startOfDay());
    });
@endphp

<style>
    .step-circle { transition: all 0.3s ease; }
    .step-circle-base { width: 3rem; height: 3rem; font-size: 1rem; }
    .step-circle-active { width: 3.5rem; height: 3.5rem; font-size: 1.5rem; animation: pulse 2s infinite; }
    .step-text { transition: all 0.3s ease; }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 20px rgba(250, 204, 21, 0.6); }
        50% { box-shadow: 0 0 40px rgba(250, 204, 21, 1); }
    }

    .progress-bar {
        background: linear-gradient(90deg, #9333ea, #ec4899);
        transition: width 0.8s ease;
        box-shadow: 0 0 15px rgba(147, 51, 234, 0.6);
    }

    .glass-effect {
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(148, 163, 184, 0.3);
    }

    .filter-inactive {
        background: rgba(45, 53, 72, 0.6);
        backdrop-filter: blur(10px) saturate(150%);
        border: 1px solid rgba(148, 163, 184, 0.2);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    }

    .progress-scrolled .step-text { opacity: 0; height: 0; margin: 0 !important; overflow: hidden; }
    .progress-scrolled .step-circle-base { width: 2.5rem !important; height: 2.5rem !important; }
    .progress-scrolled .step-circle-active { width: 3rem !important; height: 3rem !important; }

    #content-start { padding-top: calc(7rem + 1px); }

    /* Class active cho ngày và rạp */
    .date-tab.active {
        background: linear-gradient(to right, #fbbf24, #fb923c) !important;
        color: black !important;
        transform: scale(1.05);
        box-shadow: 0 10px 25px rgba(251, 146, 60, 0.4);
        border: 4px solid #fef08a;
    }
    .date-tab.early-active::after {
        content: "SỚM";
        position: absolute;
        top: -12px;
        right: -12px;
        background: #dc2626;
        color: white;
        font-size: 0.65rem;
        font-weight: 900;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        animation: pulse 2s infinite;
    }

    .cinema-filter-btn.active {
        background: linear-gradient(to right, #fbbf24, #fb923c) !important;
        color: black !important;
        box-shadow: 0 10px 25px rgba(251, 146, 60, 0.4);
        border: 4px solid #fef08a;
    }
    .cinema-filter-btn.all-active {
        background: linear-gradient(to right, #9333ea, #ec4899) !important;
        color: white !important;
        box-shadow: 0 10px 25px rgba(147, 51, 234, 0.4);
        border: 4px solid #c084fc;
    }
</style>

{{-- Thanh tiến trình (Sticky) --}}
@auth
<div id="progress-header" class="fixed top-20 left-0 right-0 z-40 bg-slate-900/95 backdrop-blur-md border-b border-white/10 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 py-3 md:py-4">
        <div class="grid grid-cols-5 gap-2 text-center mb-2">
            @for($i = 1; $i <= 5; $i++)
                @php
                    $opacityClass = $i === 1 ? '' : 'opacity-' . (100 - $i * 15);
                    $circleClass = $i === 1 ? 'step-circle-active' : 'step-circle-base';
                    $bgClass = $i === 1 ? 'bg-gradient-to-r from-purple-500 to-pink-500 ring-2 ring-purple-400/50' : 'bg-white/10';
                    $textClass = $i === 1 ? 'text-white' : 'text-gray-' . (400 + $i * 50);
                    $steps = ['Chọn rạp & suất chiếu', 'Chọn ghế', 'Combo', 'Xác nhận', 'Thanh toán'];
                @endphp
                <div class="flex flex-col items-center {{ $opacityClass }}">
                    <div class="step-circle {{ $circleClass }} rounded-full flex items-center justify-center font-black text-white {{ $bgClass }} shadow-xl">{{ $i }}</div>
                    <p class="mt-1 text-xs font-bold {{ $textClass }} step-text">{{ $steps[$i-1] }}</p>
                </div>
            @endfor
        </div>
        <div class="relative h-1 bg-white/10 rounded-full overflow-hidden">
            <div class="progress-bar h-full rounded-full" style="width: 20%"></div>
        </div>
    </div>
</div>
@endauth

<div id="content-start">
    {{-- Banner suất chiếu sớm --}}
    @if($hasEarlyPremiere && $earlyDate)
        <div class="relative overflow-hidden bg-gradient-to-r from-yellow-400 via-orange-500 to-pink-500 py-10 md:py-12">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="absolute top-0 left-0 w-64 h-64 bg-yellow-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
            <div class="relative max-w-6xl mx-auto px-4 text-center">
                <div class="inline-flex flex-col md:flex-row items-center justify-center gap-4 bg-black/60 backdrop-blur-xl px-8 py-6 rounded-3xl shadow-2xl border-4 border-yellow-300">
                    <svg class="w-16 h-16 text-yellow-300 animate-bounce" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.953a1 1 0 00.95.69h4.163c.969 0 1.371 1.24.588 1.81l-3.374 2.456a1 1 0 00-.364 1.118l1.287 3.953c.3.921-.755 1.688-1.54 1.118l-3.374-2.456a1 1 0 00-1.175 0L6.337 18.69c-.784.57-1.838-.197-1.54-1.118l1.287-3.953a1 1 0 00-.364-1.118L2.346 9.38c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.953z"/>
                    </svg>
                    <div class="text-white">
                        <h2 class="text-3xl md:text-5xl font-heading font-black drop-shadow-2xl">SUẤT CHIẾU SỚM ĐẶC BIỆT</h2>
                        <p class="text-2xl md:text-4xl font-bold mt-2 text-yellow-100">{{ $earlyDate->translatedFormat('D, d/m/y') }}</p>
                        <p class="text-lg mt-3 opacity-90">Đặt vé ngay để là người đầu tiên thưởng thức!</p>
                    </div>
                </div>
            </div>
        </div>
    @elseif($movie->early_premiere_date && \Carbon\Carbon::parse($movie->early_premiere_date)->isPast())
        <div class="bg-gradient-to-r from-gray-700 to-gray-900 py-4 text-center">
            <p class="text-lg font-bold text-gray-300">Suất chiếu sớm đã diễn ra vào {{ \Carbon\Carbon::parse($movie->early_premiere_date)->translatedFormat('d/m/Y') }}</p>
        </div>
    @endif
</div>

{{-- Thông tin phim --}}
<div class="bg-gradient-to-br from-slate-900 via-slate-800 to-black text-white py-16 px-4">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl md:text-6xl font-heading font-black text-center text-yellow-400 mb-12 drop-shadow-2xl">{{ $movie->title }}</h1>
        <div class="grid lg:grid-cols-3 gap-10 items-start">
            <div class="lg:col-span-1">
                <div class="rounded-3xl shadow-2xl overflow-hidden border-8 border-purple-500/60">
                    <img src="{{ $movie->poster ? asset('poster/' . basename($movie->poster)) : 'https://via.placeholder.com/600x900' }}"
                         alt="{{ $movie->title }}" class="w-full h-auto object-cover">
                </div>
            </div>
            <div class="lg:col-span-2 space-y-8">
                <div class="glass-effect rounded-3xl p-8 border border-white/20">
                    <h3 class="text-2xl font-black text-white mb-6 border-b border-white/10 pb-3">Thông tin phim</h3>
                    <div class="grid md:grid-cols-2 gap-6 text-lg">
                        <div>
                            <p><span class="font-bold text-gray-300">Đạo diễn:</span> <span class="text-yellow-300 font-semibold ml-3">{{ $movie->director }}</span></p>
                            <p><span class="font-bold text-gray-300">Thể loại:</span> <span class="text-white font-medium ml-3">{{ $movie->category->name ?? 'Phim lẻ' }}</span></p>
                            <p><span class="font-bold text-gray-300">Thời lượng:</span> <span class="text-white font-medium ml-3">{{ $movie->duration }} phút</span></p>
                        </div>
                        <div>
                            <p><span class="font-bold text-gray-300">Giới hạn tuổi:</span> <span class="bg-red-600 px-6 py-3 ml-4 rounded-full font-black text-2xl shadow-lg inline-block">{{ $movie->age_limit ?? 'T' }}+</span></p>
                            <p><span class="font-bold text-gray-300">Khởi chiếu:</span> <span class="text-yellow-400 font-black text-2xl ml-4">{{ $releaseDate ? $releaseDate->translatedFormat('d/m/Y') : 'Chưa công bố' }}</span></p>
                        </div>
                    </div>
                </div>
                <div class="glass-effect rounded-3xl p-8 border border-white/20">
                    <h3 class="text-2xl font-black text-white mb-6 border-b border-white/10 pb-3">Nội dung phim</h3>
                    <p class="text-lg leading-relaxed text-gray-100">{{ $movie->description }}</p>
                </div>
                @if($movie->trailer)
                    <div>
                        <h3 class="text-3xl font-black mb-6">Trailer chính thức</h3>
                        <div class="relative w-full rounded-3xl overflow-hidden shadow-2xl border-4 border-purple-500/50" style="padding-bottom: 56.25%;">
                            <iframe class="absolute inset-0 w-full h-full" src="https://www.youtube.com/embed/{{ \Illuminate\Support\Str::afterLast($movie->trailer, 'v=') }}" allowfullscreen></iframe>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($hasUpcomingShows)
    {{-- Bộ lọc --}}
    <div class="py-12 px-4 bg-slate-900/50 backdrop-blur-sm border-t border-b border-white/10">
        <div class="max-w-6xl mx-auto">
            {{-- Lọc theo rạp --}}
            <div class="mb-10">
                <h3 class="text-white font-bold text-3xl mb-6 text-center md:text-left border-b-2 border-purple-500/50 pb-2 inline-block">Lọc theo rạp</h3>
                <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                    <a href="{{ route('movie.detail', $movie->slug) }}"
                       data-cinema=""
                       class="cinema-filter-btn group relative px-8 py-4 rounded-full font-extrabold text-lg transition-all duration-300 transform hover:scale-[1.03] filter-inactive text-gray-300 hover:bg-white/10 hover:text-white {{ is_null($selectedCinemaId) ? 'active all-active' : '' }}">
                        <span class="relative z-10 flex items-center gap-2">Tất cả rạp</span>
                    </a>

                    @forelse($availableCinemas as $cinema)
                        <a href="{{ route('movie.detail', [$movie->slug, 'cinema' => $cinema->cinema_id]) }}"
                           data-cinema="{{ $cinema->cinema_id }}"
                           class="cinema-filter-btn group relative px-8 py-4 rounded-full font-extrabold text-lg transition-all duration-300 transform hover:scale-[1.03] filter-inactive text-gray-300 hover:bg-white/10 hover:text-white {{ $selectedCinemaId == $cinema->cinema_id ? 'active' : '' }}">
                            <span class="relative z-10">{{ $cinema->cinema_name }}</span>
                        </a>
                    @empty
                        <div class="text-gray-400 text-center w-full py-8 font-medium">Không có rạp nào đang chiếu phim này</div>
                    @endforelse
                </div>
            </div>

            <div class="my-12 border-t border-white/10"></div>

            {{-- Chọn ngày --}}
            <div>
                <h3 class="text-white font-bold text-3xl mb-6 text-center md:text-left border-b-2 border-yellow-500/50 pb-2 inline-block">Chọn ngày chiếu</h3>
                <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                    @php
                        $uniqueDates = $availableDates->filter(function ($date) {
                            return \Carbon\Carbon::parse($date)->gte(now()->startOfDay());
                        })->unique()->values();

                        if ($earlyDate && $earlyDate->gte(now()->startOfDay())) {
                            $uniqueDates = $uniqueDates->sortBy(function ($date) use ($earlyDate) {
                                return \Carbon\Carbon::parse($date)->toDateString() === $earlyDate->toDateString() ? -1 : 1;
                            })->values();
                        }
                    @endphp

                    @foreach($uniqueDates as $dateStr)
                        @php
                            $d = \Carbon\Carbon::parse($dateStr);
                            $isActive = $d->toDateString() === $selectedDate->toDateString();
                            $isEarly = $earlyDate && $d->toDateString() === $earlyDate->toDateString();
                        @endphp
                        <a href="{{ url()->current() }}?date={{ $d->format('Y-m-d') }}{{ $selectedCinemaId ? '&cinema=' . $selectedCinemaId : '' }}"
                           data-date="{{ $d->format('Y-m-d') }}"
                           class="date-tab relative w-[100px] h-[110px] flex flex-col justify-center items-center rounded-2xl font-bold text-center transition-all duration-300 transform filter-inactive text-gray-300 hover:bg-white/10 hover:text-white {{ $isActive ? 'active' : '' }} {{ $isActive && $isEarly ? 'early-active' : '' }}">
                            <div class="text-4xl font-black">{{ $d->format('d') }}</div>
                            <div class="text-sm uppercase font-extrabold mt-1">{{ $d->translatedFormat('D') }}</div>
                            <div class="text-xs mt-0.5">{{ $d->translatedFormat('M, Y') }}</div>
                            @if($isEarly && !$isActive)
                                <span class="absolute -top-3 -right-3 bg-red-600 text-white text-xs px-2 py-0.5 rounded-full font-black animate-pulse shadow-lg z-10">SỚM</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Container suất chiếu - AJAX sẽ thay thế nội dung bên trong div này --}}
    <div class="py-16 bg-gradient-to-br from-slate-800 to-black px-4" data-shows-container>
        <div class="max-w-6xl mx-auto">
            {{-- Nội dung ban đầu render server-side để đồng bộ --}}
            @php
                $currentCinemaId = $selectedCinemaId;
                $titleCinema = $selectedCinemaId 
                    ? ($availableCinemas->firstWhere('cinema_id', $selectedCinemaId)->cinema_name ?? 'Rạp không xác định') 
                    : 'tất cả rạp';
            @endphp
            <h2 class="text-4xl font-heading font-black text-center text-white mb-12">
                Suất chiếu - {{ $titleCinema }}
            </h2>

            @forelse($shows as $cinemaId => $showGroup)
                @php $cinema = $showGroup->first()->cinema @endphp
                <div class="mb-12">
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-t-3xl p-6 md:p-8">
                        <h3 class="text-2xl md:text-3xl font-heading font-extrabold text-white">{{ $cinema->cinema_name }}</h3>
                        <p class="mt-2 text-purple-100 text-sm opacity-90">{{ $cinema->address }}</p>
                    </div>
                    <div class="bg-slate-700/50 backdrop-blur-sm rounded-b-3xl p-8">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach($showGroup as $show)
                                @php $startTime = \Carbon\Carbon::parse($show->start_time); @endphp

                                @auth
                                    <a href="{{ route('seat.selection', $show->show_id) }}"
                                    class="group/show relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-600 to-pink-600 p-4 text-white text-center shadow-lg transform transition-all duration-300 hover:scale-110 hover:shadow-2xl hover:shadow-purple-500/60">
                                        <div class="text-3xl md:text-4xl font-black tracking-tight">{{ $startTime->format('H:i') }}</div>
                                        <div class="mt-3 space-y-2">
                                            <p class="text-xs md:text-sm font-bold opacity-95 truncate">{{ $show->room->room_name ?? 'Phòng' }}</p>
                                            <p class="text-xs bg-white/25 px-3 py-1.5 rounded-full inline-block font-semibold">{{ $show->remaining_seats }} ghế</p>
                                        </div>
                                        <div class="absolute inset-0 bg-white opacity-0 group-hover/show:opacity-10 transition-opacity duration-500 pointer-events-none rounded-2xl"></div>
                                    </a>
                                @else
                                    <a href="{{ route('login') }}?redirect={{ url()->current() }}"
                                    class="relative overflow-hidden rounded-2xl bg-gray-50 border-3 border-dashed border-gray-300 p-4 text-center text-gray-600 font-bold hover:border-purple-400 hover:bg-purple-50 transition-all duration-300 flex flex-col justify-center items-center min-h-[140px]">
                                        <div class="text-3xl md:text-4xl font-black">{{ $startTime->format('H:i') }}</div>
                                        <div class="mt-3 text-xs md:text-sm">Đăng nhập</div>
                                    </a>
                                @endauth
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-24">
                    <div class="inline-block bg-white/10 backdrop-blur-xl rounded-3xl px-16 py-16 border border-white/20">
                        <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 16a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-3xl md:text-4xl font-heading font-extrabold text-gray-300">Không có suất chiếu</p>
                        <p class="text-gray-400 mt-3 text-lg">Chọn ngày hoặc rạp khác</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@else
    {{-- Không có suất chiếu --}}
    <div class="py-32 bg-gradient-to-b from-slate-900 to-black px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="inline-block bg-white/10 backdrop-blur-xl rounded-3xl px-16 py-20 border border-white/20 shadow-2xl">
                @if(!$isReleased)
                    <h2 class="text-5xl md:text-4xl font-black text-purple-400 mb-6">SẮP CHIẾU</h2>
                    <p class="text-2xl text-gray-300 mb-8">Phim sẽ chính thức khởi chiếu vào</p>
                    <p class="text-6xl font-black text-yellow-400">{{ $releaseDate?->translatedFormat('d/m/Y') ?? 'Chưa xác định' }}</p>
                    <p class="text-xl text-gray-400 mt-8">Hãy theo dõi để đặt vé sớm nhất!</p>
                @else
                    <h2 class="text-5xl md:text-4xl font-black text-red-500 mb-6">HIỆN TẠI CHƯA CÓ SUẤT CHIẾU</h2>
                    <p class="text-2xl text-gray-300">Cảm ơn bạn đã quan tâm đến bộ phim này! Bạn có thể xem danh sách phim có suất chiếu bằng liên kết dưới đây</p>
                    <a href="{{ route('movies.all', ['tab' => 'showing']) }}"
                    class="inline-block mt-10 px-12 py-5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-xl rounded-full hover:scale-105 transition-transform shadow-2xl">
                        Xem phim đang chiếu
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif

@if($hasUpcomingShows)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const progressHeader = document.getElementById('progress-header');
        if (progressHeader) {
            window.addEventListener('scroll', () => {
                progressHeader.classList.toggle('progress-scrolled', window.scrollY > 100);
            });
        }

        function updateActiveStates(date = null, cinema = null) {
            // Cập nhật ngày
            document.querySelectorAll('.date-tab').forEach(tab => {
                tab.classList.toggle('active', tab.dataset.date === date);
                tab.classList.toggle('early-active', tab.dataset.date === date && tab.innerHTML.includes('SỚM'));
            });

            // Cập nhật rạp
            document.querySelectorAll('.cinema-filter-btn').forEach(btn => {
                const btnCinema = btn.dataset.cinema || null;
                btn.classList.remove('active', 'all-active');

                if (cinema === null && btnCinema === '') {
                    btn.classList.add('active', 'all-active');
                } else if (String(btnCinema) === String(cinema)) {
                    btn.classList.add('active');
                }
            });
        }

        function loadShowsAjax(date, cinema = null) {
            const slug = '{{ $movie->slug }}';
            let ajaxUrl = `/phim/${slug}/suat-chieu`;
            const params = new URLSearchParams();
            if (date) params.append('date', date);
            if (cinema !== null && cinema !== '') params.append('cinema', cinema);
            if (params.toString()) ajaxUrl += '?' + params.toString();

            const container = document.querySelector('[data-shows-container] > .max-w-6xl.mx-auto');
            if (!container) return;

            container.innerHTML = '<div class="text-center py-20"><p class="text-white text-2xl">Đang tải...</p></div>';

            fetch(ajaxUrl)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.text();
                })
                .then(html => {
                    container.innerHTML = html;
                    updateActiveStates(date, cinema || null);
                })
                .catch(err => {
                    console.error('Lỗi AJAX:', err);
                    container.innerHTML = '<div class="text-center py-20"><p class="text-red-500 text-2xl">Lỗi tải dữ liệu</p></div>';
                });
        }

        // Event delegation cho click ngày và rạp
        document.addEventListener('click', function(e) {
            const dateLink = e.target.closest('a.date-tab');
            const cinemaLink = e.target.closest('a.cinema-filter-btn');

            if (dateLink) {
                e.preventDefault();
                const date = dateLink.dataset.date;
                const currentCinema = new URLSearchParams(window.location.search).get('cinema');

                loadShowsAjax(date, currentCinema || null);

                const newUrl = new URL(window.location);
                newUrl.searchParams.set('date', date);
                if (!currentCinema) newUrl.searchParams.delete('cinema');
                window.history.pushState({}, '', newUrl);
            }

            if (cinemaLink) {
                e.preventDefault();
                const cinema = cinemaLink.dataset.cinema || null;
                let currentDate = new URLSearchParams(window.location.search).get('date');
                if (!currentDate) {
                    currentDate = document.querySelector('.date-tab.active')?.dataset.date ||
                                   document.querySelector('a.date-tab')?.dataset.date;
                }

                loadShowsAjax(currentDate, cinema);

                window.history.pushState({}, '', cinemaLink.href);
            }
        });

        // Khởi tạo trạng thái active
        const initDate = new URLSearchParams(window.location.search).get('date') ||
                         document.querySelector('.date-tab.active')?.dataset.date ||
                         document.querySelector('a.date-tab')?.dataset.date;
        const initCinema = new URLSearchParams(window.location.search).get('cinema');
        updateActiveStates(initDate, initCinema);
    });
</script>
@endif
@endsection