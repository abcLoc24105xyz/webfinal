{{-- resources/views/movie/partials/showtimes.blade.php --}}
{{-- File này chỉ chứa phần danh sách suất chiếu để AJAX trả về --}}
<div class="max-w-6xl mx-auto">
    <h2 class="text-4xl font-heading font-black text-center text-white mb-12">
        {{ request()->has('cinema') ? 'Suất chiếu - ' . ($availableCinemas->firstWhere('cinema_id', request('cinema'))->cinema_name ?? '') : 'Suất chiếu tất cả rạp' }}
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="text-3xl md:text-4xl font-heading font-extrabold text-gray-300">Không có suất chiếu</p>
                <p class="text-gray-400 mt-3 text-lg">Chọn ngày hoặc rạp khác</p>
            </div>
        </div>
    @endforelse
</div>