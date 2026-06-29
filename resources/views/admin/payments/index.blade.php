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
                <a href="{{ route('admin.payments.index', ['status' => 'paid']) }}"
                   class="px-4 py-2 rounded-xl text-sm font-bold border transition
                          {{ request('status') === 'paid' ? 'bg-green-500 border-green-400 text-black' : 'bg-white/10 border-white/20 text-green-300 hover:bg-white/20' }}">
                    <i class="fas fa-check-circle mr-1"></i> Đã duyệt
                </a>
                <a href="{{ route('admin.payments.index', ['status' => 'cancelled']) }}"
                   class="px-4 py-2 rounded-xl text-sm font-bold border transition
                          {{ request('status') === 'cancelled' ? 'bg-red-500 border-red-400 text-white' : 'bg-white/10 border-white/20 text-red-300 hover:bg-white/20' }}">
                    <i class="fas fa-times-circle mr-1"></i> Đã hủy
                </a>
            </div>
        </div>

        {{-- THÔNG BÁO --}}
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

        {{-- BẢNG --}}
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
                    <tbody class="divide-y divide-white/10">
                        @forelse($reservations as $r)
                        <tr class="hover:bg-white/5 transition duration-200">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <a href="{{ route('admin.payments.show', $r->booking_code) }}"
                                   class="text-pink-400 font-bold hover:underline text-xs">
                                    {{ $r->booking_code }}
                                </a>
                                @if($r->ticket_code)
                                    <div class="text-xs text-purple-400 mt-0.5">🎟 {{ $r->ticket_code }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-semibold text-white">{{ $r->user->full_name ?? '—' }}</div>
                                <div class="text-xs text-purple-300">{{ $r->user->email ?? '' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if($r->show && $r->show->movie)
                                    <div class="font-semibold text-white max-w-[160px] truncate">{{ $r->show->movie->title }}</div>
                                    <div class="text-xs text-purple-300">
                                        {{ optional($r->show->show_date)->format('d/m/Y') }}
                                        {{ substr($r->show->start_time ?? '', 0, 5) }}
                                    </div>
                                @else
                                    <span class="text-purple-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right font-bold text-yellow-300">
                                {{ number_format($r->total_amount) }} đ
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-xs text-purple-200 uppercase">
                                {{ $r->payment_method ?? '—' }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                @php
                                    $badges = [
                                        'pending'   => ['bg-yellow-500/20 text-yellow-200 border-yellow-500', 'fa-clock',        'Chờ duyệt'],
                                        'paid'      => ['bg-green-500/20 text-green-200 border-green-500',   'fa-check-circle', 'Đã duyệt'],
                                        'cancelled' => ['bg-red-500/20 text-red-200 border-red-500',         'fa-times-circle', 'Đã hủy'],
                                        'expired'   => ['bg-gray-500/20 text-gray-300 border-gray-500',      'fa-hourglass-end','Hết hạn'],
                                    ];
                                    [$cls, $icon, $label] = $badges[$r->status] ?? ['bg-gray-500/20 text-gray-300 border-gray-500', 'fa-question', $r->status];
                                @endphp
                                <span class="px-3 py-1 rounded-full font-bold text-xs border {{ $cls }}">
                                    <i class="fas {{ $icon }} mr-1"></i> {{ $label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-xs text-purple-300">
                                {{ optional($r->created_at)->format('d/m/Y H:i') }}
                                @if($r->paid_at)
                                    <div class="text-green-300">✓ {{ optional($r->paid_at)->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.payments.show', $r->booking_code) }}"
                                       class="px-3 py-1.5 rounded-lg bg-indigo-500/20 border border-indigo-400 text-indigo-300 hover:bg-indigo-500/40 transition text-xs font-bold">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($r->status === 'pending')
                                        <form action="{{ route('admin.payments.confirm', $r->booking_code) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Duyệt đơn {{ $r->booking_code }}?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-green-500/20 border border-green-400 text-green-300 hover:bg-green-500/40 transition text-xs font-bold">
                                                <i class="fas fa-check mr-1"></i> Duyệt
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($r->status, ['pending', 'paid']))
                                        <form action="{{ route('admin.payments.cancel', $r->booking_code) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Hủy đơn {{ $r->booking_code }}?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-lg bg-red-500/20 border border-red-400 text-red-300 hover:bg-red-500/40 transition text-xs font-bold">
                                                <i class="fas fa-times mr-1"></i> Hủy
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

        @if($reservations->hasPages())
            <div class="mt-8 flex justify-center">
                <div class="bg-white/10 backdrop-blur-xl rounded-xl border border-white/20 p-4">
                    {{ $reservations->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            </div>
        @endif

    </div>
</div>
@endsection