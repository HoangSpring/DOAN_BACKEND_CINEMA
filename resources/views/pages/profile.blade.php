@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-dark text-white font-body pt-24 pb-16">
    <div class="max-w-2xl mx-auto px-6">
        
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-marquee text-white">Thông Tin Tài Khoản</h1>
            <p class="text-slate-400 mt-2">Quản lý thông tin cá nhân và bảo mật tài khoản của bạn.</p>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-4 rounded-xl mb-8 flex items-center gap-3">
                <i class="fas fa-check-circle text-lg"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-dark-card border border-dark-border rounded-2xl p-6 sm:p-8 shadow-2xl">
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="full_name" class="block text-sm font-medium text-slate-300 mb-2">Họ và tên</label>
                    <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" 
                        class="w-full bg-black/50 border border-dark-border rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                    @error('full_name')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-300 mb-2">Số điện thoại</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
                        class="w-full bg-black/50 border border-dark-border rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                    @error('phone')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Địa chỉ Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" 
                        class="w-full bg-black/50 border border-dark-border rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                    @error('email')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-6 border-t border-dark-border">
                    <h3 class="text-lg font-medium text-white mb-1">Đổi mật khẩu</h3>
                    <p class="text-xs text-slate-400 mb-4">Chỉ điền nếu bạn muốn thay đổi mật khẩu hiện tại.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Mật khẩu mới</label>
                            <input type="password" id="password" name="password" placeholder="••••••••"
                                class="w-full bg-black/50 border border-dark-border rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                            @error('password')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">Xác nhận mật khẩu mới</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••"
                                class="w-full bg-black/50 border border-dark-border rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                        </div>
                    </div>
                </div>

                <div class="pt-6 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto btn-primary px-8 py-3 rounded-xl font-semibold shadow-glow-primary hover:-translate-y-0.5 transition-all">
                        <i class="fas fa-save mr-2"></i> Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
