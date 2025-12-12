{{-- resources/views/booking/payment-failed.blade.php --}}
@extends('layouts.app')
@section('title', 'Thanh toán thất bại')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-slate-900 to-black flex items-center justify-center px-4 py-20">
    <div class="max-w-2xl w-full">
        
        {{-- ANIMATION ICON --}}
        <div class="mb-8 flex justify-center">
            <div class="relative w-24 h-24">
                <svg class="w-full h-full animate-pulse" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="11" stroke="#ef4444" stroke-width="2" class="opacity-50"></circle>
                </svg>
                <svg class="absolute inset-0 w-full h-full" fill="none" viewBox="0 0 24 24">
                    <path d="M9 5L19 15M19 5L9 15" stroke="#ef4444" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        {{-- MAIN CARD --}}
        <div class="bg-white/10 backdrop-blur-xl rounded-3xl border border-red-500/30 p-8 md:p-12 shadow-2xl text-center">
            
            {{-- TITLE --}}
            <h1 class="text-4xl md:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-pink-500 mb-4">
                Thanh Toán Thất Bại
            </h1>

            {{-- ERROR MESSAGE --}}
            <p class="text-xl text-gray-300 mb-8">
                {{ session('error') ?? 'Có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại.' }}
            </p>

            {{-- ERROR DETAILS --}}
            @if(session('payment_error_details'))
                <div class="bg-red-600/20 border border-red-500 rounded-2xl p-6 mb-8 text-left">
                    <p class="text-red-300 font-bold mb-3">Chi tiết lỗi:</p>
                    <div class="text-sm text-gray-300 space-y-2">
                        @if(is_array(session('payment_error_details')))
                            @foreach(session('payment_error_details') as $key => $value)
                                <div class="flex justify-between">
                                    <span class="text-gray-400">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                    <span class="text-red-400 font-mono">{{ $value }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-red-400 font-mono">{{ session('payment_error_details') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- BOOKING INFO IF AVAILABLE --}}
            @if(session('booking_code'))
                <div class="bg-white/5 rounded-2xl p-6 mb-8">
                    <p class="text-gray-400 text-sm mb-2">Mã đặt vé của bạn:</p>
                    <p class="text-2xl font-black text-yellow-400 font-mono">{{ session('booking_code') }}</p>
                    <p class="text-gray-400 text-xs mt-3">Vui lòng lưu mã này để theo dõi đơn hàng</p>
                </div>
            @endif

            {{-- REASON & SUGGESTION --}}
            <div class="bg-white/5 rounded-2xl p-6 mb-8 text-left">
                <p class="text-gray-300 font-bold mb-3">Nguyên nhân có thể:</p>
                <ul class="text-gray-400 space-y-2 text-sm">
                    <li class="flex items-start gap-2">
                        <span class="text-yellow-400 mt-1">•</span>
                        <span>Khoảng thời gian giữ ghế đã hết (hơn 15 phút)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-yellow-400 mt-1">•</span>
                        <span>Lỗi kết nối mạng hoặc MoMo tạm bị gián đoạn</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-yellow-400 mt-1">•</span>
                        <span>Hủy giao dịch từ ứng dụng MoMo hoặc ngân hàng</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-yellow-400 mt-1">•</span>
                        <span>Ghế đã được đặt bởi người khác</span>
                    </li>
                </ul>
            </div>

            {{-- ACTIONS --}}
            <div class="space-y-3">
                {{-- RETRY BUTTON --}}
                <a href="{{ route('booking.summary') }}" 
                   class="block w-full bg-gradient-to-r from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white py-4 rounded-2xl font-black text-lg shadow-xl transition transform hover:scale-105">
                    ↻ Thử Lại Thanh Toán
                </a>

                {{-- CHOOSE SEAT AGAIN BUTTON --}}
                @if(session('show_id'))
                    <a href="{{ route('seat.selection', session('show_id')) }}" 
                       class="block w-full bg-white/10 hover:bg-white/20 text-white py-4 rounded-2xl font-bold text-lg shadow-xl transition border border-white/30">
                        ← Chọn Ghế Lại
                    </a>
                @endif

                {{-- HOME BUTTON --}}
                <a href="{{ route('home') }}" 
                   class="block w-full bg-gray-800 hover:bg-gray-700 text-white py-4 rounded-2xl font-bold text-lg shadow-xl transition">
                    ← Quay Về Trang Chủ
                </a>
            </div>
        </div>

        {{-- SUPPORT INFO --}}
        <div class="mt-8 text-center text-gray-400 text-sm">
            <p>Cần hỗ trợ? <a href="{{ route('advertise') }}" class="text-purple-400 hover:text-purple-300 font-bold">Liên hệ với chúng tôi</a></p>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .bg-white\/10 {
        animation: fadeIn 0.6s ease-out forwards;
    }
</style>
@endsection