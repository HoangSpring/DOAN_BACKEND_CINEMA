<!DOCTYPE html>
<html lang="vi" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HoangCinema - Đặt Vé Xem Phim</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#e50914',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
        }
    </style>

</head>

<body class="antialiased min-h-screen flex flex-col" x-data="{ showTrailer: false, trailerUrl: '' }">

    <header class="sticky top-0 z-50 w-full border-b border-slate-800 bg-slate-900/95 backdrop-blur">
        <div class="container mx-auto flex h-16 items-center justify-between px-4">
            <a href="{{ route('home') }}" class="flex items-center space-x-2 text-primary">
                <img src="{{ asset('logo-hoangcinema.svg') }}" alt="HoangCinema" class="h-10 sm:h-12 w-auto">
            </a>

            <nav class="flex items-center space-x-6 text-sm font-medium">
                @php $currentStatus = request('status', 'showing'); @endphp
                <a href="{{ route('home') }}"
                    class="transition-colors hover:text-white {{ $currentStatus === 'showing' && !request()->has('status') ? 'text-white' : 'text-slate-300' }}">Trang
                    chủ</a>
                <a href="{{ route('home', ['status' => 'showing']) }}"
                    class="transition-colors hover:text-white {{ $currentStatus === 'showing' ? 'text-white' : 'text-slate-400' }}">Phim
                    đang chiếu</a>
                <a href="{{ route('home', ['status' => 'coming_soon']) }}"
                    class="transition-colors hover:text-white {{ $currentStatus === 'coming_soon' ? 'text-white' : 'text-slate-400' }}">Phim
                    sắp chiếu</a>
                @auth
                    <a href="{{ route('my-tickets') }}"
                        class="transition-colors hover:text-white {{ request()->routeIs('my-tickets') ? 'text-white' : 'text-slate-400' }}">Vé
                        của tôi</a>
                @endauth
            </nav>

            <div class="flex items-center space-x-4 relative" x-data="{ open: false }">
                @auth
                    @if(in_array(auth()->user()->role, ['admin', 'staff']))
                        <a href="{{ auth()->user()->role === 'admin' ? route('admin.reports.index') : route('staff.counter') }}"
                            class="hidden sm:inline-flex items-center rounded-md border border-primary/40 bg-primary/10 px-3 py-2 text-sm font-medium text-primary hover:bg-primary/20 transition">
                            {{ auth()->user()->role === 'admin' ? 'Trang quản trị' : 'Giao diện nhân viên' }}
                        </a>
                    @endif

                    <button @click="open = !open" @click.away="open = false"
                        class="relative h-8 w-8 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-user">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                    </button>
                    <div x-show="open" style="display: none;"
                        class="absolute right-0 top-10 mt-2 w-56 rounded-md border border-slate-800 bg-slate-900 p-1 shadow-lg">
                        <div class="px-2 py-1.5 text-sm font-semibold">
                            {{ auth()->user()->name }}
                        </div>
                        <div class="h-px bg-slate-800 my-1"></div>
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.reports.index') }}"
                                class="block rounded-sm px-2 py-1.5 text-sm hover:bg-slate-800">Trang quản trị</a>
                        @endif
                        @if(auth()->user()->role === 'staff')
                            <a href="{{ route('staff.counter') }}"
                                class="block rounded-sm px-2 py-1.5 text-sm hover:bg-slate-800">Giao diện nhân viên</a>
                        @endif
                        <a href="#" class="block rounded-sm px-2 py-1.5 text-sm hover:bg-slate-800">Vé của tôi</a>
                        <div class="h-px bg-slate-800 my-1"></div>
                        <form method="POST" action="{{ Route::has('logout') ? route('logout') : url('/logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left rounded-sm px-2 py-1.5 text-sm text-red-500 hover:bg-slate-800">Đăng
                                xuất</button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-2">
                        <a href="{{ Route::has('register') ? route('register') : url('/register') }}"
                            class="hidden sm:inline-flex items-center justify-center border border-white/10 bg-white/5 text-white px-4 py-2 rounded-md text-sm font-medium transition hover:bg-white/10">Đăng
                            ký</a>
                        <a href="{{ Route::has('login') ? route('login') : url('/login') }}"
                            class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">Đăng
                            nhập</a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-slate-950 text-slate-300 mt-12 border-t border-slate-800 w-full pt-16 pb-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12 text-left">
                <!-- Brand & Intro -->
                <div>
                    <div class="flex items-center space-x-2 text-white mb-6">
                        <img src="{{ asset('logo-hoangcinema.svg') }}" alt="HoangCinema" class="h-14 w-auto">
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed mb-6">
                        Trải nghiệm điện ảnh đỉnh cao với hệ thống phòng chiếu hiện đại, âm thanh sống động và dịch vụ
                        hàng đầu. Mang Hollywood đến gần bạn hơn.
                    </p>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-slate-400">Ngôn ngữ:</span>
                        <button
                            class="flex items-center gap-2 bg-slate-800 hover:bg-slate-700 transition px-3 py-1.5 rounded-md text-white text-xs font-semibold">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/2/21/Flag_of_Vietnam.svg" alt="VN"
                                class="w-4 h-3 object-cover rounded-sm">
                            Tiếng Việt
                        </button>
                    </div>
                </div>

                <!-- Policies -->
                <div>
                    <h4 class="text-white font-bold uppercase tracking-wider text-sm mb-6 flex items-center gap-2">
                        <span class="w-2 h-4 bg-primary rounded-full"></span>
                        Chính Sách & Quy Định
                    </h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#"
                                class="text-slate-400 hover:text-primary transition-colors flex items-center gap-2"><i
                                    class="fas fa-chevron-right text-[10px]"></i> Điều khoản sử dụng</a></li>
                        <li><a href="#"
                                class="text-slate-400 hover:text-primary transition-colors flex items-center gap-2"><i
                                    class="fas fa-chevron-right text-[10px]"></i> Chính sách thanh toán</a></li>
                        <li><a href="#"
                                class="text-slate-400 hover:text-primary transition-colors flex items-center gap-2"><i
                                    class="fas fa-chevron-right text-[10px]"></i> Chính sách bảo mật</a></li>
                        <li><a href="#"
                                class="text-slate-400 hover:text-primary transition-colors flex items-center gap-2"><i
                                    class="fas fa-chevron-right text-[10px]"></i> Chính sách hoàn vé</a></li>
                    </ul>
                </div>

                <!-- Customer Support -->
                <div>
                    <h4 class="text-white font-bold uppercase tracking-wider text-sm mb-6 flex items-center gap-2">
                        <span class="w-2 h-4 bg-primary rounded-full"></span>
                        Chăm Sóc Khách Hàng
                    </h4>
                    <ul class="space-y-4 text-sm text-slate-400">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-phone-alt mt-1 text-primary"></i>
                            <div>
                                <p class="text-xs uppercase tracking-wider mb-1">Hotline Hỗ trợ</p>
                                <p class="text-white font-bold text-lg">1900 1234</p>
                            </div>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-clock text-primary"></i>
                            <span>Giờ làm việc: 8:00 - 22:00</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-envelope text-primary"></i>
                            <a href="mailto:support@cinemagic.vn"
                                class="hover:text-primary transition-colors">support@cinemagic.vn</a>
                        </li>
                    </ul>
                </div>

                <!-- Socials & Certs -->
                <div>
                    <h4 class="text-white font-bold uppercase tracking-wider text-sm mb-6 flex items-center gap-2">
                        <span class="w-2 h-4 bg-primary rounded-full"></span>
                        Kết Nối Với Chúng Tôi
                    </h4>
                    <div class="flex items-center gap-3 mb-8">
                        <a href="#"
                            class="w-10 h-10 bg-slate-800 hover:bg-primary text-white rounded-full flex items-center justify-center transition-all shadow-lg hover:shadow-primary/50 hover:-translate-y-1">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#"
                            class="w-10 h-10 bg-slate-800 hover:bg-primary text-white rounded-full flex items-center justify-center transition-all shadow-lg hover:shadow-primary/50 hover:-translate-y-1">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#"
                            class="w-10 h-10 bg-slate-800 hover:bg-primary text-white rounded-full flex items-center justify-center transition-all shadow-lg hover:shadow-primary/50 hover:-translate-y-1">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="#"
                            class="w-10 h-10 bg-slate-800 hover:bg-primary text-white rounded-full flex items-center justify-center font-bold text-xs transition-all shadow-lg hover:shadow-primary/50 hover:-translate-y-1">
                            Zalo
                        </a>
                    </div>
                    <div class="opacity-80 hover:opacity-100 transition-opacity">
                        <img src="https://hotro.tiki.vn/hc/article_attachments/900003058863/bct.png"
                            alt="Đã Thông Báo BCT" class="h-10">
                    </div>
                </div>

            </div>

            <div
                class="border-t border-slate-800 pt-8 mt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-slate-500">
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

    <!-- Trailer Modal -->
    <div x-show="showTrailer" style="display: none;"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
        x-transition.opacity>
        <div class="relative w-full max-w-5xl rounded-2xl bg-black overflow-hidden shadow-2xl border border-slate-800"
            @click.away="showTrailer = false; $refs.trailerVideo.pause()">
            <button @click="showTrailer = false; $refs.trailerVideo.pause()"
                class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-slate-900/80 hover:bg-primary text-white flex items-center justify-center transition border border-white/10">
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