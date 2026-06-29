{{-- resources/views/admin/payments/show.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'Chi Tiết Đơn Hàng')
@section('subtitle', 'Xem thông tin chi tiết và duyệt đơn hàng.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 p-6 sm:p-8 text-white">
    <div class="max-w-5xl mx-auto">

        {{-- BACK --}}
        <a href="{{ route('admin.payments.index') }}"
           class="inline-flex items-center gap-2 text-purple-300 hover:text-white transition mb-6 font-semibold">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>

        {{-- THÔNG BÁO --}}
        @if(session('success'))
            <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 border-2 border-green-500 text-green-200 px-5 py-3 rounded-xl mb-6 text-sm font-bold flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-gradient-to-r from-red-500/20 to-pink-500/20 border-2 border-red-500 text-red-200 px-5 py-3 rounded-xl mb-6 text-sm font-bold flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-xl"></i> {{ session('error') }}
            </div>
        @endif

        {{-- HEADER CARD --}}
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6 mb-6">
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <h2 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 mb-1">
                        Đơn hàng: {{ $payment->booking_code }}
                    </h2>
                    @if($payment->reservation && $payment->reservation->ticket_code)
                        <p class="text-purple-300 text-sm">🎟 Mã vé: <span class="font-bold text-white">{{ $payment->reservation->ticket_code }}</span></p>
                    @endif
                    <p class="text-purple-400 text-xs mt-1">Tạo lúc: {{ optional($payment->created_at)->format('H:i d/m/Y') }}</p>
                </div>

                {{-- TRẠNG THÁI --}}
                <div class="flex flex-col items-end gap-3">
                    @if($payment->status === 'pending')
                        <span class="px-5 py-2 rounded-full font-black text-sm bg-yellow-500/20 text-yellow-200 border-2 border-yellow-500">
                            <i class="fas fa-clock mr-2"></i> Chờ duyệt
                        </span>
                    @elseif($payment->status === 'completed')
                        <span class="px-5 py-2 rounded-full font-black text-sm bg-green-500/20 text-green-200 border-2 border-green-500">
                            <i class="fas fa-check-circle mr-2"></i> Đã duyệt
                        </span>
                        @if($payment->paid_at)
                            <p class="text-green-400 text-xs">Duyệt lúc: {{ optional($payment->paid_at)->format('H:i d/m/Y') }}</p>
                        @endif
                    @elseif($payment->status === 'cancelled')
                        <span class="px-5 py-2 rounded-full font-black text-sm bg-red-500/20 text-red-200 border-2 border-red-500">
                            <i class="fas fa-times-circle mr-2"></i> Đã hủy
                        </span>
                    @endif

                    {{-- ACTION BUTTONS --}}
                    <div class="flex gap-2">
                        @if($payment->status === 'pending')
                            <form action="{{ route('admin.payments.confirm', $payment) }}" method="POST"
                                  onsubmit="return confirm('Xác nhận DUYỆT đơn hàng này?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="px-5 py-2 rounded-xl bg-green-500 hover:bg-green-400 text-black font-black transition shadow-lg shadow-green-500/30">
                                    <i class="fas fa-check mr-2"></i> Duyệt ngay
                                </button>
                            </form>
                            <form action="{{ route('admin.payments.cancel', $payment) }}" method="POST"
                                  onsubmit="return confirm('Xác nhận HỦY đơn hàng này?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="px-5 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/40 border border-red-400 text-red-300 font-black transition">
                                    <i class="fas fa-times mr-2"></i> Hủy đơn
                                </button>
                            </form>
                        @elseif($payment->status === 'completed')
                            <form action="{{ route('admin.payments.cancel', $payment) }}" method="POST"
                                  onsubmit="return confirm('Đơn này đã được duyệt. Bạn chắc chắn muốn HỦY?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="px-5 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/40 border border-red-400 text-red-300 font-black transition">
                                    <i class="fas fa-ban mr-2"></i> Hủy đơn
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            {{-- THÔNG TIN KHÁCH HÀNG --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6">
                <h3 class="text-lg font-black text-purple-300 mb-4 flex items-center gap-2">
                    <i class="fas fa-user text-pink-400"></i> Thông tin khách hàng
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center border-b border-white/10 pb-2">
                        <span class="text-purple-400">Họ tên:</span>
                        <span class="font-bold text-white">{{ $payment->user->full_name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-white/10 pb-2">
                        <span class="text-purple-400">Email:</span>
                        <span class="font-bold text-white">{{ $payment->user->email ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-purple-400">Điện thoại:</span>
                        <span class="font-bold text-white">{{ $payment->user->phone ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- THÔNG TIN THANH TOÁN --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6">
                <h3 class="text-lg font-black text-purple-300 mb-4 flex items-center gap-2">
                    <i class="fas fa-credit-card text-pink-400"></i> Thông tin thanh toán
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center border-b border-white/10 pb-2">
                        <span class="text-purple-400">Phương thức:</span>
                        <span class="font-bold text-white uppercase">{{ $payment->payment_method ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-white/10 pb-2">
                        <span class="text-purple-400">Mã giao dịch:</span>
                        <span class="font-mono text-xs text-pink-300">{{ $payment->order_id ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-purple-400">Tổng tiền:</span>
                        <span class="font-black text-yellow-300 text-lg">{{ number_format($payment->amount) }} đ</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- THÔNG TIN SUẤT CHIẾU --}}
        @if($payment->reservation && $payment->reservation->show)
        @php $show = $payment->reservation->show; @endphp
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6 mb-6">
            <h3 class="text-lg font-black text-purple-300 mb-4 flex items-center gap-2">
                <i class="fas fa-film text-pink-400"></i> Thông tin suất chiếu
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div class="bg-white/5 rounded-xl p-4">
                    <p class="text-purple-400 text-xs mb-1">Phim</p>
                    <p class="font-bold text-white">{{ optional($show->movie)->title ?? '—' }}</p>
                </div>
                <div class="bg-white/5 rounded-xl p-4">
                    <p class="text-purple-400 text-xs mb-1">Ngày chiếu</p>
                    <p class="font-bold text-white">{{ optional($show->show_date)->format('d/m/Y') ?? '—' }}</p>
                    <p class="text-purple-300 text-xs">{{ $show->start_time ?? '' }}</p>
                </div>
                <div class="bg-white/5 rounded-xl p-4">
                    <p class="text-purple-400 text-xs mb-1">Phòng chiếu</p>
                    <p class="font-bold text-white">{{ optional($show->room)->name ?? '—' }}</p>
                    <p class="text-purple-300 text-xs">{{ optional(optional($show->room)->cinema)->name ?? '' }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- GHẾ ĐÃ ĐẶT --}}
        @if($payment->reservation && $payment->reservation->seats->count())
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6 mb-6">
            <h3 class="text-lg font-black text-purple-300 mb-4 flex items-center gap-2">
                <i class="fas fa-chair text-pink-400"></i> Ghế đã đặt
                <span class="text-sm font-normal text-purple-400">({{ $payment->reservation->seats->count() }} ghế)</span>
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($payment->reservation->seats as $seat)
                    <span class="px-4 py-2 rounded-lg bg-purple-500/20 border border-purple-400 text-white font-bold text-sm">
                        {{ $seat->seat_code ?? $seat->seat_id }}
                        <span class="text-purple-300 text-xs ml-1">{{ number_format($seat->pivot->seat_price) }}đ</span>
                    </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- COMBO ĐÃ CHỌN --}}
        @if($payment->reservation && $payment->reservation->combos->count())
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6 mb-6">
            <h3 class="text-lg font-black text-purple-300 mb-4 flex items-center gap-2">
                <i class="fas fa-glass-whiskey text-pink-400"></i> Combo đã chọn
            </h3>
            <div class="space-y-2">
                @foreach($payment->reservation->combos as $combo)
                    <div class="flex justify-between items-center bg-white/5 rounded-xl px-4 py-3">
                        <div>
                            <span class="font-bold text-white">{{ $combo->name }}</span>
                            <span class="text-purple-400 text-sm ml-2">x{{ $combo->pivot->quantity }}</span>
                        </div>
                        <span class="text-yellow-300 font-bold">{{ number_format($combo->pivot->combo_price * $combo->pivot->quantity) }} đ</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection