@extends('layouts.app')

@section('title', 'Đăng ký tài khoản')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center py-12 px-4">
    <div class="max-w-lg w-full bg-white/95 backdrop-blur rounded-3xl shadow-2xl p-10">
        <div class="text-center mb-8">
            <h2 class="text-4xl font-bold text-gray-800">Tạo tài khoản mới</h2>
            <p class="text-gray-600 mt-3">Chỉ mất 30 giây để tham gia cộng đồng GhienCine</p>
        </div>

        {{-- Hiển thị thông báo thành công / lỗi --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-center font-medium animate-pulse">
                <strong>✓ Thành công!</strong><br>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-center font-medium animate-pulse">
                <strong>✗ Lỗi!</strong><br>
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
            <strong>Yêu cầu mật khẩu:</strong>
            <ul class="mt-2 space-y-1 text-xs">
                <li>✓ Tối thiểu 8 ký tự</li>
                <li>✓ Chứa chữ hoa (A-Z)</li>
                <li>✓ Chứa chữ thường (a-z)</li>
                <li>✓ Chứa số (0-9)</li>
                <li>✓ Chứa ký tự đặc biệt (@$!%*?&)</li>
            </ul>
        </div>

        <form action="{{ route('register') }}" method="POST" id="registerForm">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Họ và tên</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                           class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-purple-500 @error('full_name') border-red-500 @enderror">
                    @error('full_name') <small class="text-red-600 text-xs block mt-1">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-purple-500 @error('email') border-red-500 @enderror">
                    @error('email') <small class="text-red-600 text-xs block mt-1">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="0901234567" maxlength="10" required
                           class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-purple-500 @error('phone') border-red-500 @enderror">
                    @error('phone') <small class="text-red-600 text-xs block mt-1">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu</label>
                    <input type="password" name="password" required id="passwordInput"
                           class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-purple-500 @error('password') border-red-500 @enderror">
                    @error('password') <small class="text-red-600 text-xs block mt-1">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu</label>
                    <input type="password" name="password_confirmation" required id="passwordConfirmInput"
                           class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            {{-- Thông báo mật khẩu khớp / không khớp --}}
            <div id="passwordMatchIndicator" class="mt-4 p-3 bg-gray-100 rounded-lg text-sm hidden">
                <span id="matchText" class="text-gray-600">Mật khẩu xác nhận không khớp</span>
            </div>

            <button type="submit" id="submitBtn" class="w-full mt-8 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-lg py-4 rounded-xl hover:shadow-2xl transition">
                Đăng ký ngay & nhận mã OTP
            </button>
        </form>

        <p class="text-center mt-6 text-gray-600">
            Đã có tài khoản? <a href="{{ route('login') }}" class="text-purple-600 font-bold hover:underline">Đăng nhập</a>
        </p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const passwordInput = document.getElementById('passwordInput');
        const passwordConfirmInput = document.getElementById('passwordConfirmInput');
        const matchIndicator = document.getElementById('passwordMatchIndicator');
        const matchText = document.getElementById('matchText');

        function checkPasswordMatch() {
            if (passwordInput.value.length === 0 || passwordConfirmInput.value.length === 0) {
                matchIndicator.classList.add('hidden');
                submitBtn.disabled = false;
                return;
            }

            if (passwordInput.value === passwordConfirmInput.value) {
                matchIndicator.classList.remove('hidden');
                matchText.textContent = '✓ Mật khẩu khớp';
                matchText.className = 'text-green-600 font-medium';
                submitBtn.disabled = false;
            } else {
                matchIndicator.classList.remove('hidden');
                matchText.textContent = '✗ Mật khẩu xác nhận không khớp';
                matchText.className = 'text-red-600 font-medium';
                submitBtn.disabled = true;
            }
        }

        // Kiểm tra khi nhập
        passwordInput.addEventListener('input', checkPasswordMatch);
        passwordConfirmInput.addEventListener('input', checkPasswordMatch);

        // Khi submit: chỉ đổi nút thành "Đang xử lý..." (không preventDefault để form submit bình thường)
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Đang xử lý... <span class="animate-spin inline-block ml-2">⟳</span>';
        });
    });
</script>
@endsection