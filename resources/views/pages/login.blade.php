@extends('layouts.customer')

@section('content')
    <div class="container mx-auto px-4 py-16 flex justify-center items-center font-body">
        <div class="w-full max-w-md bg-dark-card border border-dark-border rounded-2xl shadow-2xl p-8 liquid-glass">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-marquee text-white mb-2">Đăng nhập</h1>
                <p class="text-slate-400">Vui lòng đăng nhập để đặt vé xem phim</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/50 text-red-500 px-4 py-3 rounded-lg mb-6">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        class="w-full bg-dark border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all placeholder-slate-500"
                        placeholder="Nhập email của bạn">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Mật khẩu</label>
                    <input type="password" name="password" id="password" required
                        class="w-full bg-dark border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all placeholder-slate-500"
                        placeholder="••••••••">
                </div>

                <button type="submit" class="btn-primary w-full flex justify-center items-center gap-2">
                    Đăng nhập
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-slate-400">
                <p>Chưa có tài khoản? <a href="{{ url('/register') }}" class="text-primary hover:underline">Đăng ký
                        ngay</a></p>
            </div>
        </div>
    </div>
@endsection