<!DOCTYPE html>
<html lang="vi" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HoangCinema - Đặt Vé Xem Phim</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased min-h-screen flex flex-col font-body bg-dark text-white"
    x-data="{ showTrailer: false, trailerUrl: '' }">

    {{-- HEADER tối giản sang trọng theo chuẩn Cinestar --}}
    <header class="sticky top-0 z-50 w-full border-b border-dark-border bg-dark/90 backdrop-blur-md">
        <div class="container mx-auto flex h-20 items-center justify-between px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center space-x-2">
                <img src="{{ asset('logo-hoangcinema.svg') }}" alt="HoangCinema"
                    class="h-12 sm:h-14 w-auto object-contain">
            </a>

            <nav class="hidden md:flex items-center space-x-8 text-sm font-semibold tracking-wide">
                @php $currentStatus = request('status'); @endphp

                {{-- 1. Trang chủ: Chỉ sáng khi KHÔNG có tham số status trên URL và không ở trang vé của tôi --}}
                <a href="{{ route('home') }}"
                    class="transition-colors duration-300 hover:text-primary {{ is_null($currentStatus) && !request()->routeIs('my-tickets') ? 'text-primary font-bold' : 'text-slate-400' }}">
                    Trang chủ
                </a>

                {{-- 2. Phim đang chiếu: Chỉ sáng khi status chính xác là 'showing' --}}
                <a href="{{ route('home', ['status' => 'showing']) }}"
                    class="transition-colors duration-300 hover:text-primary {{ $currentStatus === 'showing' ? 'text-primary font-bold' : 'text-slate-400' }}">
                    Phim đang chiếu
                </a>

                {{-- 3. Phim sắp chiếu: Chỉ sáng khi status chính xác là 'coming_soon' --}}
                <a href="{{ route('home', ['status' => 'coming_soon']) }}"
                    class="transition-colors duration-300 hover:text-primary {{ $currentStatus === 'coming_soon' ? 'text-primary font-bold' : 'text-slate-400' }}">
                    Phim sắp chiếu
                </a>

                @auth
                    <a href="{{ route('my-tickets') }}"
                        class="transition-colors duration-300 hover:text-primary {{ request()->routeIs('my-tickets') ? 'text-primary font-bold' : 'text-slate-400' }}">
                        Vé của tôi
                    </a>
                @endauth
            </nav>

            <div class="flex items-center space-x-4 relative" x-data="{ open: false }">
                @auth
                    @if(in_array(auth()->user()->role, ['admin', 'staff']))
                        <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('staff.counter') }}"
                            class="hidden sm:inline-flex items-center rounded-full border border-primary/40 bg-primary/10 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-primary hover:bg-primary/20 transition duration-300">
                            {{ auth()->user()->role === 'admin' ? 'Trang quản trị' : 'Giao diện nhân viên' }}
                        </a>
                    @endif

                    <button @click="open = !open" @click.away="open = false"
                        class="relative h-10 w-10 rounded-full bg-dark-card border border-dark-border hover:border-primary hover:text-primary flex items-center justify-center transition duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-user">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div x-show="open" style="display: none;"
                        class="absolute right-0 top-12 mt-2 w-56 rounded-2xl border border-dark-border bg-dark-card p-2 shadow-2xl z-50"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100">
                        <div class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Tài khoản: {{ auth()->user()->name }}
                        </div>
                        <div class="h-px bg-dark-border my-1"></div>
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}"
                                class="block rounded-xl px-3 py-2 text-sm text-slate-200 hover:bg-black/40 hover:text-primary transition">Trang
                                quản trị</a>
                        @endif
                        @if(auth()->user()->role === 'staff')
                            <a href="{{ route('staff.counter') }}"
                                class="block rounded-xl px-3 py-2 text-sm text-slate-200 hover:bg-black/40 hover:text-primary transition">Giao
                                diện nhân viên</a>
                        @endif
                        <a href="{{ route('my-tickets') }}"
                            class="block rounded-xl px-3 py-2 text-sm text-slate-200 hover:bg-black/40 hover:text-primary transition">Vé
                            của tôi</a>
                        <div class="h-px bg-dark-border my-1"></div>
                        <form method="POST" action="{{ Route::has('logout') ? route('logout') : url('/logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left rounded-xl px-3 py-2 text-sm text-red-400 hover:bg-red-500/10 transition">Đăng
                                xuất</button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ Route::has('register') ? route('register') : url('/register') }}"
                            class="hidden sm:inline-flex items-center justify-center border border-white/10 bg-white/5 text-white px-5 py-2 rounded-full text-sm font-semibold transition duration-300 hover:bg-white/10">Đăng
                            ký</a>
                        <a href="{{ Route::has('login') ? route('login') : url('/login') }}"
                            class="inline-flex items-center justify-center bg-primary text-black px-5 py-2 rounded-full text-sm font-bold tracking-wide transition duration-300 hover:shadow-glow-primary hover:scale-[1.03]">Đăng
                            nhập</a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- FOOTER đồng nhất phong cách Dark Elegant --}}
    <footer class="bg-dark-card text-slate-300 mt-20 border-t border-dark-border w-full pt-20 pb-8 font-body">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16 text-left">
                <div class="space-y-6">
                    <div class="flex items-center space-x-2 text-white">
                        <img src="{{ asset('logo-hoangcinema.svg') }}" alt="HoangCinema"
                            class="h-14 w-auto object-contain">
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed font-light">
                        Trải nghiệm điện ảnh đỉnh cao với hệ thống phòng chiếu hiện đại, âm thanh sống động và dịch vụ
                        hàng đầu. Mang Hollywood đến gần bạn hơn.
                    </p>
                    <div class="flex items-center gap-3 text-sm">
                        <span class="text-slate-500">Ngôn ngữ:</span>
                        <button
                            class="flex items-center gap-2 bg-black/40 hover:bg-black/60 border border-dark-border transition px-3 py-2 rounded-xl text-white text-xs font-semibold">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/2/21/Flag_of_Vietnam.svg" alt="VN"
                                class="w-4 h-3 object-cover rounded-sm">
                            Tiếng Việt
                        </button>
                    </div>
                </div>

                <div>
                    <h4 class="text-white font-marquee tracking-wider text-base uppercase mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-primary rounded-full"></span>
                        Chính Sách & Quy Định
                    </h4>
                    <ul class="space-y-4 text-sm font-light text-slate-400">
                        <li><a href="#" class="hover:text-primary transition-colors flex items-center gap-2 group">
                                <i
                                    class="fas fa-chevron-right text-[8px] text-primary transition-transform duration-300 group-hover:translate-x-1"></i>
                                Điều khoản sử dụng</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors flex items-center gap-2 group">
                                <i
                                    class="fas fa-chevron-right text-[8px] text-primary transition-transform duration-300 group-hover:translate-x-1"></i>
                                Chính sách thanh toán</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors flex items-center gap-2 group">
                                <i
                                    class="fas fa-chevron-right text-[8px] text-primary transition-transform duration-300 group-hover:translate-x-1"></i>
                                Chính sách bảo mật</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors flex items-center gap-2 group">
                                <i
                                    class="fas fa-chevron-right text-[8px] text-primary transition-transform duration-300 group-hover:translate-x-1"></i>
                                Chính sách hoàn vé</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-marquee tracking-wider text-base uppercase mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-primary rounded-full"></span>
                        Chăm Sóc Khách Hàng
                    </h4>
                    <ul class="space-y-5 text-sm text-slate-400 font-light">
                        <li class="flex items-start gap-3.5">
                            <i class="fas fa-phone-alt mt-1 text-primary"></i>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-slate-500 mb-0.5">Hotline Hỗ trợ</p>
                                <p class="text-white font-bold text-lg">1900 1234</p>
                            </div>
                        </li>
                        <li class="flex items-center gap-3.5">
                            <i class="fas fa-clock text-primary"></i>
                            <span>Giờ làm việc: 8:00 - 23:30</span>
                        </li>
                        <li class="flex items-center gap-3.5">
                            <i class="fas fa-envelope text-primary"></i>
                            <a href="mailto:support@hoangcinema.vn"
                                class="hover:text-primary transition-colors">support@hoangcinema.vn</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-marquee tracking-wider text-base uppercase mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-primary rounded-full"></span>
                        Kết Nối Với Chúng Tôi
                    </h4>
                    <div class="flex items-center gap-3 mb-8">
                        <a href="#"
                            class="w-10 h-10 bg-black/40 border border-dark-border hover:bg-primary hover:text-black text-white rounded-full flex items-center justify-center transition-all duration-300 shadow-lg hover:shadow-glow-primary hover:-translate-y-1">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#"
                            class="w-10 h-10 bg-black/40 border border-dark-border hover:bg-primary hover:text-black text-white rounded-full flex items-center justify-center transition-all duration-300 shadow-lg hover:shadow-glow-primary hover:-translate-y-1">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#"
                            class="w-10 h-10 bg-black/40 border border-dark-border hover:bg-primary hover:text-black text-white rounded-full flex items-center justify-center transition-all duration-300 shadow-lg hover:shadow-glow-primary hover:-translate-y-1">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="#"
                            class="w-10 h-10 bg-black/40 border border-dark-border hover:bg-primary hover:text-black text-white rounded-full flex items-center justify-center font-bold text-[10px] transition-all duration-300 shadow-lg hover:shadow-glow-primary hover:-translate-y-1">
                            Zalo
                        </a>
                    </div>
                </div>
            </div>

            <div
                class="border-t border-dark-border pt-8 mt-8 flex flex-col md:flex-row justify-between items-center gap-4 font-light text-slate-500">
                <p class="text-sm">
                    &copy; {{ date('Y') }} HoangCinema. All rights reserved.
                </p>
                <p class="text-xs text-slate-600">
                    Công ty Cổ phần Điện ảnh HoangCinema Việt Nam. Giấy CNĐKDN: 0123456789.
                </p>
            </div>
        </div>
    </footer>

    @stack('scripts')
    @include('components.toast')

    <div x-show="showTrailer" style="display: none;"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
        x-transition.opacity>
        <div class="relative w-full max-w-5xl rounded-3xl bg-black overflow-hidden shadow-2xl border border-white/5"
            @click.away="showTrailer = false; $refs.trailerVideo.pause()">
            <button @click="showTrailer = false; $refs.trailerVideo.pause()"
                class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-black/80 hover:bg-primary hover:text-black text-white flex items-center justify-center transition border border-white/10 shadow-lg">
                <i class="fas fa-times"></i>
            </button>
            <div class="aspect-video w-full bg-black flex items-center justify-center">
                <template x-if="showTrailer">
                    <video x-ref="trailerVideo" :src="trailerUrl" controls autoplay
                        class="w-full h-full object-contain"></video>
                </template>
            </div>
        </div>
    </div>
</body>

</html>