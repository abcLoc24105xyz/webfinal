{{-- resources/views/admin/payments/show.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'Chi Tiết Đơn Hàng')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 p-6 sm:p-8 text-white">
    <div class="max-w-5xl mx-auto">

        <a href="{{ route('admin.payments.index') }}"
           class="inline-flex items-center gap-2 text-purple-300 hover:text-white transition mb-6 font-semibold">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>

        @if(session('success'))
            <div class="bg-green-500/20 border-2 border-green-500 text-green-200 px-5 py-3 rounded-xl mb-6 text-sm font-bold flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-500/20 border-2 border-red-500 text-red-200 px-5 py-3 rounded-xl mb-6 text-sm font-bold flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-xl"></i> {{ session('error') }}
            </div>
        @endif

        {{-- HEADER CARD --}}
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6 mb-6">
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <h2 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 mb-1">
                        {{ $reservation->booking_code }}
                    </h2>
                    @if($reservation->ticket_code)
                        <p class="text-purple-300 text-sm">🎟 Mã vé: <span class="font-bold text-white">{{ $reservation->ticket_code }}</span></p>
                    @endif
                    <p class="text-purple-400 text-xs mt-1">Tạo lúc: {{ optional($reservation->created_at)->format('H:i d/m/Y') }}</p>
                </div>
                <div class="flex flex-col items-end gap-3">
                    @php
                        $badges = [
                            'pending'   => ['bg-yellow-500/20 text-yellow-200 border-yellow-500', 'Chờ duyệt'],
                            'confirmed' => ['bg-green-500/20 text-green-200 border-green-500',   'Đã duyệt'],
                            'paid'      => ['bg-blue-500/20 text-blue-200 border-blue-500',      'Đã thanh toán'],
                            'cancelled' => ['bg-red-500/20 text-red-200 border-red-500',         'Đã hủy'],
                            'expired'   => ['bg-gray-500/20 text-gray-300 border-gray-500',      'Hết hạn'],
                        ];
                        [$cls, $label] = $badges[$reservation->status] ?? ['bg-gray-500/20 text-gray-300 border-gray-500', $reservation->status];
                    @endphp
                    <span class="px-5 py-2 rounded-full font-black text-sm border-2 {{ $cls }}">{{ $label }}</span>

                    <div class="flex gap-2">
                        @if($reservation->status === 'pending')
                            <form action="{{ route('admin.payments.confirm', $reservation->booking_code) }}" method="POST"
                                  onsubmit="return confirm('Duyệt đơn hàng này?')">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="px-5 py-2 rounded-xl bg-green-500 hover:bg-green-400 text-black font-black transition shadow-lg shadow-green-500/30">
                                    <i class="fas fa-check mr-2"></i> Duyệt ngay
                                </button>
                            </form>
                        @endif
                        @if(in_array($reservation->status, ['pending', 'confirmed', 'paid']))
                            <form action="{{ route('admin.payments.cancel', $reservation->booking_code) }}" method="POST"
                                  onsubmit="return confirm('Hủy đơn hàng này?')">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="px-5 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/40 border border-red-400 text-red-300 font-black transition">
                                    <i class="fas fa-times mr-2"></i> Hủy đơn
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            {{-- KHÁCH HÀNG --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6">
                <h3 class="text-lg font-black text-purple-300 mb-4"><i class="fas fa-user text-pink-400 mr-2"></i>Khách hàng</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between border-b border-white/10 pb-2">
                        <span class="text-purple-400">Họ tên</span>
                        <span class="font-bold">{{ $reservation->user->full_name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-white/10 pb-2">
                        <span class="text-purple-400">Email</span>
                        <span class="font-bold">{{ $reservation->user->email ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-purple-400">Điện thoại</span>
                        <span class="font-bold">{{ $reservation->user->phone ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- THANH TOÁN --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6">
                <h3 class="text-lg font-black text-purple-300 mb-4"><i class="fas fa-credit-card text-pink-400 mr-2"></i>Thanh toán</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between border-b border-white/10 pb-2">
                        <span class="text-purple-400">Phương thức</span>
                        <span class="font-bold uppercase">{{ $reservation->payment_method ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-white/10 pb-2">
                        <span class="text-purple-400">Thời gian duyệt</span>
                        <span class="font-bold">{{ optional($reservation->paid_at)->format('H:i d/m/Y') ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-purple-400">Tổng tiền</span>
                        <span class="font-black text-yellow-300 text-lg">{{ number_format($reservation->total_amount) }} đ</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- SUẤT CHIẾU --}}
        @if($reservation->show)
        @php $show = $reservation->show; @endphp
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6 mb-6">
            <h3 class="text-lg font-black text-purple-300 mb-4"><i class="fas fa-film text-pink-400 mr-2"></i>Suất chiếu</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div class="bg-white/5 rounded-xl p-4">
                    <p class="text-purple-400 text-xs mb-1">Phim</p>
                    <p class="font-bold">{{ optional($show->movie)->title ?? '—' }}</p>
                </div>
                <div class="bg-white/5 rounded-xl p-4">
                    <p class="text-purple-400 text-xs mb-1">Ngày & Giờ</p>
                    <p class="font-bold">{{ optional($show->show_date)->format('d/m/Y') ?? '—' }}</p>
                    <p class="text-purple-300 text-xs">{{ substr($show->start_time ?? '', 0, 5) }} → {{ substr($show->end_time ?? '', 0, 5) }}</p>
                </div>
                <div class="bg-white/5 rounded-xl p-4">
                    <p class="text-purple-400 text-xs mb-1">Phòng chiếu</p>
                    <p class="font-bold">{{ optional($show->room)->name ?? '—' }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- GHẾ --}}
        @if($reservation->seats->count())
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6 mb-6">
            <h3 class="text-lg font-black text-purple-300 mb-4">
                <i class="fas fa-chair text-pink-400 mr-2"></i>Ghế đã đặt
                <span class="text-sm font-normal text-purple-400">({{ $reservation->seats->count() }} ghế)</span>
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($reservation->seats as $seat)
                    <span class="px-4 py-2 rounded-lg bg-purple-500/20 border border-purple-400 font-bold text-sm">
                        {{ $seat->seat_num ?? $seat->seat_id }}
                        <span class="text-purple-300 text-xs ml-1">{{ number_format($seat->pivot->seat_price) }}đ</span>
                    </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- COMBO --}}
        @if($reservation->combos->count())
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6">
            <h3 class="text-lg font-black text-purple-300 mb-4"><i class="fas fa-glass-whiskey text-pink-400 mr-2"></i>Combo</h3>
            <div class="space-y-2">
                @foreach($reservation->combos as $combo)
                    <div class="flex justify-between items-center bg-white/5 rounded-xl px-4 py-3">
                        <span class="font-bold">{{ $combo->combo_name ?? $combo->name }} <span class="text-purple-400 font-normal">x{{ $combo->pivot->quantity }}</span></span>
                        <span class="text-yellow-300 font-bold">{{ number_format($combo->pivot->combo_price * $combo->pivot->quantity) }} đ</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection