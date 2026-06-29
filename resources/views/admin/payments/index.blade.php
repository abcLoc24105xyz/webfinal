{{-- resources/views/admin/payments/index.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'Duyệt Đơn Hàng')
@section('subtitle', 'Xem xét và duyệt thủ công các đơn đặt vé của khách hàng.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 p-6 sm:p-8 text-white">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER --}}
        <div class="flex flex-wrap justify-between items-center mb-8 gap-4">
            <h1 class="text-4xl sm:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">
                <i class="fas fa-clipboard-check mr-2 text-indigo-400"></i> DUYỆT ĐƠN HÀNG
            </h1>
            <div class="flex items-center gap-3 flex-wrap">
                {{-- Bộ lọc trạng thái --}}
                <div class="flex gap-2 flex-wrap">
                    <a href="{{ route('admin.payments.index') }}"
                       class="px-4 py-2 rounded-xl text-sm font-bold border transition
                              {{ !request('status') ? 'bg-purple-600 border-purple-400 text-white' : 'bg-white/10 border-white/20 text-purple-300 hover:bg-white/20' }}">
                        Tất cả
                    </a>
                    <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}"
                       class="px-4 py-2 rounded-xl text-sm font-bold border transition
                              {{ request('status') === 'pending' ? 'bg-yellow-500 border-yellow-400 text-black' : 'bg-white/10 border-white/20 text-yellow-300 hover:bg-white/20' }}">
                        <i class="fas fa-clock mr-1"></i> Chờ duyệt
                    </a>
                    <a href="{{ route('admin.payments.index', ['status' => 'completed']) }}"
                       class="px-4 py-2 rounded-xl text-sm font-bold border transition
                              {{ request('status') === 'completed' ? 'bg-green-500 border-green-400 text-black' : 'bg-white/10 border-white/20 text-green-300 hover:bg-white/20' }}">
                        <i class="fas fa-check-circle mr-1"></i> Đã duyệt
                    </a>
                    <a href="{{ route('admin.payments.index', ['status' => 'cancelled']) }}"
                       class="px-4 py-2 rounded-xl text-sm font-bold border transition
                              {{ request('status') === 'cancelled' ? 'bg-red-500 border-red-400 text-white' : 'bg-white/10 border-white/20 text-red-300 hover:bg-white/20' }}">
                        <i class="fas fa-times-circle mr-1"></i> Đã hủy
                    </a>
                </div>
            </div>
        </div>

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

        {{-- BẢNG DANH SÁCH --}}
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-xl overflow-hidden border border-white/20">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/20 text-white text-sm">
                    <thead class="bg-gradient-to-r from-purple-600/40 to-pink-600/40">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider">Mã đặt vé</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider">Khách hàng</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider">Phim</th>
                            <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider">Số tiền</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider">Phương thức</th>
                            <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider">Trạng thái</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider">Thời gian</th>
                            <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-transparent divide-y divide-white/10">
                        @forelse($payments as $payment)
                        <tr class="hover:bg-white/5 transition duration-200">
                            {{-- Mã đặt vé --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <a href="{{ route('admin.payments.show', $payment) }}"
                                   class="text-pink-400 font-bold hover:text-pink-300 hover:underline text-xs">
                                    {{ $payment->booking_code }}
                                </a>
                                @if($payment->reservation && $payment->reservation->ticket_code)
                                    <div class="text-xs text-purple-400 mt-0.5">
                                        🎟 {{ $payment->reservation->ticket_code }}
                                    </div>
                                @endif
                            </td>

                            {{-- Khách hàng --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-semibold text-white text-sm">{{ $payment->user->full_name ?? '—' }}</div>
                                <div class="text-xs text-purple-300">{{ $payment->user->email ?? '' }}</div>
                            </td>

                            {{-- Phim --}}
                            <td class="px-5 py-4">
                                @if($payment->reservation && $payment->reservation->show && $payment->reservation->show->movie)
                                    <div class="font-semibold text-white text-sm max-w-[180px] truncate">
                                        {{ $payment->reservation->show->movie->title }}
                                    </div>
                                    <div class="text-xs text-purple-300">
                                        {{ optional($payment->reservation->show->show_date)->format('d/m/Y') }}
                                        {{ $payment->reservation->show->start_time ?? '' }}
                                    </div>
                                @else
                                    <span class="text-purple-400 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Số tiền --}}
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <span class="font-bold text-yellow-300">
                                    {{ number_format($payment->amount) }} đ
                                </span>
                            </td>

                            {{-- Phương thức --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="text-xs font-semibold text-purple-200 uppercase">
                                    {{ $payment->payment_method ?? '—' }}
                                </span>
                            </td>

                            {{-- Trạng thái --}}
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                @if($payment->status === 'pending')
                                    <span class="px-3 py-1 rounded-full font-bold text-xs bg-yellow-500/20 text-yellow-200 border border-yellow-500">
                                        <i class="fas fa-clock mr-1"></i> Chờ duyệt
                                    </span>
                                @elseif($payment->status === 'completed')
                                    <span class="px-3 py-1 rounded-full font-bold text-xs bg-green-500/20 text-green-200 border border-green-500">
                                        <i class="fas fa-check-circle mr-1"></i> Đã duyệt
                                    </span>
                                @elseif($payment->status === 'cancelled')
                                    <span class="px-3 py-1 rounded-full font-bold text-xs bg-red-500/20 text-red-200 border border-red-500">
                                        <i class="fas fa-times-circle mr-1"></i> Đã hủy
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full font-bold text-xs bg-gray-500/20 text-gray-300 border border-gray-500">
                                        {{ $payment->status }}
                                    </span>
                                @endif
                            </td>

                            {{-- Thời gian --}}
                            <td class="px-5 py-4 whitespace-nowrap text-xs text-purple-300">
                                {{ optional($payment->created_at)->format('d/m/Y H:i') }}
                                @if($payment->paid_at)
                                    <div class="text-green-300">✓ {{ optional($payment->paid_at)->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>

                            {{-- Hành động --}}
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Nút chi tiết --}}
                                    <a href="{{ route('admin.payments.show', $payment) }}"
                                       class="px-3 py-1.5 rounded-lg bg-indigo-500/20 border border-indigo-400 text-indigo-300 hover:bg-indigo-500/40 transition text-xs font-bold"
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($payment->status === 'pending')
                                        {{-- Nút DUYỆT --}}
                                        <form action="{{ route('admin.payments.confirm', $payment) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Xác nhận DUYỆT đơn hàng {{ $payment->booking_code }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-green-500/20 border border-green-400 text-green-300 hover:bg-green-500/40 transition text-xs font-bold"
                                                    title="Duyệt đơn hàng">
                                                <i class="fas fa-check mr-1"></i> Duyệt
                                            </button>
                                        </form>

                                        {{-- Nút HỦY --}}
                                        <form action="{{ route('admin.payments.cancel', $payment) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Xác nhận HỦY đơn hàng {{ $payment->booking_code }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-red-500/20 border border-red-400 text-red-300 hover:bg-red-500/40 transition text-xs font-bold"
                                                    title="Hủy đơn hàng">
                                                <i class="fas fa-times mr-1"></i> Hủy
                                            </button>
                                        </form>
                                    @elseif($payment->status === 'completed')
                                        {{-- Chỉ cho phép hủy đơn đã duyệt --}}
                                        <form action="{{ route('admin.payments.cancel', $payment) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Đơn này đã được duyệt. Bạn chắc chắn muốn HỦY?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-red-500/20 border border-red-400 text-red-300 hover:bg-red-500/40 transition text-xs font-bold"
                                                    title="Hủy đơn hàng">
                                                <i class="fas fa-ban mr-1"></i> Hủy
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-16 text-purple-300">
                                <i class="fas fa-inbox text-5xl mb-4 block opacity-40"></i>
                                <p class="text-lg font-semibold">Không có đơn hàng nào.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Phân trang --}}
        @if($payments->hasPages())
            <div class="mt-8 flex justify-center">
                <div class="bg-white/10 backdrop-blur-xl rounded-xl border border-white/20 p-4">
                    {{ $payments->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            </div>
        @endif

    </div>
</div>
@endsection