{{-- resources/views/movie/partials/showtimes.blade.php --}}
<<<<<<< HEAD
<div class="max-w-6xl mx-auto">
    @php
        $currentCinemaId = request('cinema');
        $titleCinema = $currentCinemaId 
            ? ($availableCinemas->firstWhere('cinema_id', $currentCinemaId)->cinema_name ?? 'Rạp không xác định') 
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
=======
@forelse($shows as $cinemaId => $showGroup)
    @php 
        $cinema = $showGroup->first()->cinema;
        // Xử lý ngày chiếu sớm (nếu có)
        $isEarlyDate = $isEarlyDate ?? false;
    @endphp

    <div class="group bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-gray-100 
                hover:shadow-3xl hover:shadow-purple-500/30 transition-all duration-500 mb-10">
        
        {{-- Header rạp --}}
        <div class="bg-gradient-to-r {{ $isEarlyDate ? 'from-yellow-400 to-orange-500' : 'from-purple-600 to-pink-600' }} p-6 md:p-8">
            <h3 class="text-2xl md:text-3xl font-heading font-extrabold text-white tracking-tight">
                {{ $cinema->cinema_name }}
                @if($isEarlyDate)
                    <span class="ml-3 inline-block bg-red-600 text-white text-xs px-3 py-1 rounded-full font-black animate-pulse">
                        SUẤT SỚM
                    </span>
                @endif
            </h3>
            <p class="mt-2 text-white/90 text-sm md:text-base opacity-90 line-clamp-1">
                {{ $cinema->address }}
            </p>
        </div>

        {{-- Danh sách suất chiếu --}}
        <div class="p-6 md:p-10">
            <div class="flex flex-wrap gap-4 md:gap-6 justify-center lg:justify-start">
                @foreach($showGroup as $show)
                    @php
                        // SỬA LỖI CHÍNH: $show->start_time là string → dùng Carbon::parse()
                        $startTime = \Carbon\Carbon::parse($show->start_time);
                    @endphp

                    @auth
                        <a href="{{ route('seat.selection', $show->show_id) }}"
                           class="group/show relative overflow-hidden rounded-2xl px-8 py-6 text-center
                                  bg-gradient-to-r from-purple-600 to-pink-600 text-white
                                  shadow-xl transform transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-purple-500/50
                                  min-w-[160px]">
                            
                            <div class="text-4xl md:text-5xl font-black tracking-tight">
                                {{ $startTime->format('H:i') }}
                            </div>

                            <div class="mt-4 space-y-2">
                                <p class="text-lg font-bold opacity-90 truncate">
                                    {{ $show->room->room_name ?? 'Phòng thường' }}
                                </p>
                                <p class="text-sm bg-white/20 px-4 py-1.5 rounded-full inline-block font-medium">
                                    Còn <strong class="text-yellow-300">{{ $show->remaining_seats }}</strong> ghế
                                </p>
                            </div>

                            {{-- Hiệu ứng sáng khi hover --}}
                            <div class="absolute inset-0 bg-white opacity-0 group-hover/show:opacity-10 transition-opacity duration-500 pointer-events-none"></div>
                        </a>
                    @else
                        <a href="{{ route('login') }}?redirect={{ url()->current() }}"
                           class="flex flex-col justify-center items-center rounded-2xl px-8 py-6 text-center
                                  bg-gray-50 border-4 border-dashed border-gray-300 text-gray-600 font-bold
                                  hover:border-purple-400 hover:bg-purple-50 transition-all duration-300
                                  min-w-[160px] min-h-[140px]">
                            <div class="text-4xl md:text-5xl font-black">
                                {{ $startTime->format('H:i') }}
                            </div>
                            <div class="mt-4 text-base">Đăng nhập để đặt vé</div>
                        </a>
                    @endauth
                @endforeach
            </div>
        </div>
    </div>

@empty
    <div class="text-center py-32">
        <div class="inline-block bg-white/10 backdrop-blur-xl rounded-3xl px-16 py-20 border border-white/20">
            <svg class="w-24 h-24 mx-auto text-gray-500 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <p class="text-4xl md:text-5xl font-heading font-extrabold text-gray-400">
                Không có suất chiếu nào
            </p>
            <p class="text-xl text-gray-500 mt-6">Hãy chọn ngày hoặc rạp khác nhé!</p>
        </div>
    </div>
@endforelse
>>>>>>> 3a03ec3 (final)
