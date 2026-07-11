@extends('layouts.customer')

@section('content')
<div class="container mx-auto px-4 py-16 flex justify-center items-center">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Đăng nhập</h1>
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
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all placeholder-slate-500" 
                    placeholder="Nhập email của bạn">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Mật khẩu</label>
                <input type="password" name="password" id="password" required
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all placeholder-slate-500" 
                    placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-primary hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors flex justify-center items-center gap-2">
                Đăng nhập
            </button>
        </form>

        <div class="mt-8 text-center text-sm text-slate-400">
            <p>Chưa có tài khoản? <a href="#" class="text-primary hover:underline">Đăng ký ngay</a></p>
            <p class="mt-4 text-xs text-slate-500">
                Gợi ý: Dùng tài khoản đã sinh (seeder)<br>
                Admin: admin@example.com / password<br>
                Customer: customer1@example.com / password
            </p>
        </div>
    </div>
</div>
@endsection
