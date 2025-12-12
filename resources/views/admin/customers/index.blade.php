{{-- resources/views/admin/customers/index.blade.php --}}
@extends('admin.layouts.app')
@section('title', 'Quản Lý Khách Hàng')

@section('content')
{{-- THAY ĐỔI 1: Áp dụng nền Gradient toàn màn hình --}}
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 p-6 sm:p-8 text-white"> 
    <div class="max-w-7xl mx-auto">
        
        {{-- THAY ĐỔI 2: HEADER --}}
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl sm:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 mb-1">
                <i class="fas fa-users mr-2 text-indigo-400"></i> QUẢN LÝ KHÁCH HÀNG
            </h1>
            <div class="flex items-center space-x-4">
                {{-- Đồng bộ Total Count theo style Dark Mode --}}
                <div class="text-sm text-purple-300 bg-white/10 px-4 py-2 rounded-xl font-black border border-white/20">
                    Tổng: <strong class="text-xl text-pink-400">{{ $customers->total() }}</strong> tài khoản
                </div>
            </div>
        </div>
    
        {{-- SUCCESS/ERROR MESSAGE (Giữ nguyên style) --}}
        @if(session('success'))
            <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 border-2 border-green-500 text-green-200 px-5 py-3 rounded-xl mb-6 text-sm font-bold flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i> {{ session('success') }}
            </div>
        @endif
        
        {{-- BẢNG DANH SÁCH KHÁCH HÀNG --}}
        {{-- THAY ĐỔI 3: Áp dụng style table Dark Mode --}}
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-xl overflow-hidden border border-white/20">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/20 text-white text-sm">
                    {{-- THAY ĐỔI 4: Tiêu đề bảng --}}
                    <thead class="bg-gradient-to-r from-purple-600/40 to-pink-600/40">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-white">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-white">Họ tên & Email</th>
                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-white">Số điện thoại</th>
                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-white">Ngày đăng ký</th>
                            <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-white">Trạng thái</th>
                            <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-white">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-transparent divide-y divide-white/10">
                        @forelse($customers as $user)
                        <tr class="hover:bg-white/5 transition duration-300">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-pink-400">{{ $user->user_id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-white">{{ $user->full_name }}</div>
                                <div class="text-xs text-purple-300 italic">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-300">{{ $user->phone ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-300">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($user->status == 1)
                                    <span class="px-3 py-1 rounded-full font-bold text-xs bg-green-500/30 text-green-200 border border-green-500">
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full font-bold text-xs bg-red-500/30 text-red-200 border border-red-500">
                                        Đã khóa
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                @if($user->status == 1)
                                    <form action="{{ route('admin.customers.block', $user) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn KHÓA tài khoản của {{ $user->full_name }} không? Khách hàng sẽ không thể đăng nhập.')">
                                        @csrf
                                        {{-- Màu nút Khóa đồng bộ với style Dark Mode --}}
                                        <button type="submit" class="text-red-400 hover:text-red-300 transition duration-150 p-2 rounded-md hover:bg-red-900/50" title="Khóa tài khoản">
                                            <i class="fas fa-lock mr-1"></i> Khóa
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.customers.unblock', $user) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn MỞ KHÓA tài khoản của {{ $user->full_name }} không?')">
                                        @csrf
                                        {{-- Màu nút Mở khóa đồng bộ với style Dark Mode --}}
                                        <button type="submit" class="text-green-400 hover:text-green-300 transition duration-150 p-2 rounded-md hover:bg-green-900/50" title="Mở khóa tài khoản">
                                            <i class="fas fa-unlock mr-1"></i> Mở khóa
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-lg text-purple-300 bg-white/5">
                                <i class="fas fa-exclamation-circle mr-2"></i> Chưa có khách hàng nào được đăng ký trong hệ thống.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    
        {{-- Phân trang --}}
        @if($customers->hasPages())
            <div class="mt-8 flex justify-center">
                {{-- THAY ĐỔI 5: Style cho pagination --}}
                <div class="bg-white/10 backdrop-blur-xl rounded-xl border border-white/20 p-4">
                    {{ $customers->links('pagination::tailwind') }}
                </div>
            </div>
        @endif
        
    </div>
</div>
@endsection