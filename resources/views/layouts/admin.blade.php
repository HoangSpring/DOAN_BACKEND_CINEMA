<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - HoangCinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        cinema: {
                            gold: '#f5c518',
                            'gold-light': '#ffd700',
                            'gold-dark': '#c9a000',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #f5c518;
        }

        /* Sidebar - Giữ nguyên cấu trúc gốc */
        .sidebar-cinema {
            background: #1f2937;
            border-right: 1px solid #374151;
        }

        /* Nav Item Effects */
        .nav-item {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 8px;
            margin: 2px 12px;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%) scaleY(0);
            width: 3px;
            height: 60%;
            background: linear-gradient(180deg, #f5c518, #ffd700);
            border-radius: 0 4px 4px 0;
            transition: transform 0.3s ease;
        }

        .nav-item:hover::before,
        .nav-item.active::before {
            transform: translateY(-50%) scaleY(1);
        }

        .nav-item:hover {
            background: rgba(245, 197, 24, 0.08);
            color: #f5c518;
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(245, 197, 24, 0.15) 0%, rgba(245, 197, 24, 0.05) 100%);
            color: #f5c518;
            border: 1px solid rgba(245, 197, 24, 0.2);
            box-shadow: 0 0 20px rgba(245, 197, 24, 0.08);
        }

        .nav-item.active .nav-icon {
            color: #f5c518;
        }

        /* Section Divider */
        .section-divider {
            position: relative;
            margin: 1rem 12px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(245, 197, 24, 0.3), transparent);
        }

        .section-label {
            font-size: 0.65rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(245, 197, 24, 0.6);
            padding: 0.5rem 1.5rem;
            font-weight: 600;
        }

        /* Header */
        .header-cinema {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Content Area */
        .content-area {
            background: #f8fafc;
        }

        /* Alert Styles */
        .alert-success {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #a7f3d0;
            color: #065f46;
            border-radius: 12px;
        }

        .alert-error {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 12px;
        }

        /* Mobile Menu Button */
        .menu-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .menu-btn:hover {
            background: #fefce8;
            border-color: #f5c518;
            color: #f5c518;
        }

        /* Logout Button */
        .logout-btn {
            width: calc(100% - 24px);
            margin: 0 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #fef2f2;
            color: #ef4444;
        }

        /* Staff Badge */
        .staff-badge {
            font-size: 0.65rem;
            padding: 2px 8px;
            border-radius: 9999px;
            background: rgba(245, 197, 24, 0.15);
            color: #f5c518;
            border: 1px solid rgba(245, 197, 24, 0.25);
            font-weight: 600;
        }

        /* Bottom Info */
        .bottom-info {
            background: rgba(31, 41, 55, 0.5);
            border: 1px solid rgba(75, 85, 99, 0.5);
            border-radius: 12px;
        }
    </style>
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-100 flex h-screen" x-data="{ sidebarOpen: false }">

    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-20 transition-opacity bg-black opacity-50 lg:hidden"
        @click="sidebarOpen = false"></div>

    <!-- Sidebar - Giữ nguyên cấu trúc gốc -->
    <div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'"
        class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto transition duration-300 transform bg-gray-900 lg:translate-x-0 lg:static lg:inset-0 text-white sidebar-cinema">

        <!-- Logo - Giữ nguyên hoàn toàn -->
        <div class="flex items-center justify-center mt-8 px-4">
            <a href="{{ route('home') }}" class="hover:opacity-80 transition-opacity">
                <img src="{{ asset('logo-hoangcinema.svg') }}" alt="HoangCinema"
                    class="h-12 w-auto filter drop-shadow-md">
            </a>
        </div>

        <!-- Navigation -->
        <nav class="mt-10">
            @php
                $navItemClass = "nav-item flex items-center px-6 py-2 mt-4 text-sm font-medium transition-all duration-300";
                $activeClass = "active";
                $inactiveClass = "text-gray-400 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100";
                $iconClass = "nav-icon w-5 text-center mr-3 text-base transition-all duration-300";
            @endphp

            <a class="{{ $navItemClass }} {{ request()->routeIs('admin.dashboard') || request()->is('admin') ? $activeClass : $inactiveClass }}"
                href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-gauge {{ $iconClass }}"></i>
                Dashboard
            </a>

            <a class="{{ $navItemClass }} {{ request()->routeIs('admin.movies.*') ? $activeClass : $inactiveClass }}"
                href="{{ route('admin.movies.index') }}">
                <i class="fa-solid fa-film {{ $iconClass }}"></i>
                Phim
            </a>

            <a class="{{ $navItemClass }} {{ request()->routeIs('admin.users.*') ? $activeClass : $inactiveClass }}"
                href="{{ route('admin.users.index') }}">
                <i class="fa-solid fa-users {{ $iconClass }}"></i>
                Người dùng
            </a>

            <a class="{{ $navItemClass }} {{ request()->routeIs('admin.tags.*') ? $activeClass : $inactiveClass }}"
                href="{{ route('admin.tags.index') }}">
                <i class="fa-solid fa-tags {{ $iconClass }}"></i>
                Tag
            </a>

            <a class="{{ $navItemClass }} {{ request()->routeIs('admin.rooms.*') ? $activeClass : $inactiveClass }}"
                href="{{ route('admin.rooms.index') }}">
                <i class="fa-solid fa-chair {{ $iconClass }}"></i>
                Phòng / Ghế
            </a>

            <a class="{{ $navItemClass }} {{ request()->routeIs('admin.showtimes.*') ? $activeClass : $inactiveClass }}"
                href="{{ route('admin.showtimes.index') }}">
                <i class="fa-solid fa-clock {{ $iconClass }}"></i>
                Suất chiếu
            </a>

            <a class="{{ $navItemClass }} {{ request()->routeIs('admin.reports.*') ? $activeClass : $inactiveClass }}"
                href="{{ route('admin.reports.index') }}">
                <i class="fa-solid fa-chart-line {{ $iconClass }}"></i>
                Báo cáo
            </a>

            <!-- Section Divider -->
            <div class="section-divider"></div>
            <div class="section-label">Vận hành quầy</div>

            <a class="{{ $navItemClass }} {{ request()->routeIs('staff.counter') ? $activeClass : $inactiveClass }}"
                href="{{ route('staff.counter') }}">
                <i class="fa-solid fa-cash-register {{ $iconClass }}"></i>
                <span>Bán vé tại quầy</span>
                <span class="staff-badge ml-auto">Staff</span>
            </a>

            <a class="{{ $navItemClass }} {{ request()->routeIs('staff.checkin') ? $activeClass : $inactiveClass }}"
                href="{{ route('staff.checkin') }}">
                <i class="fa-solid fa-qrcode {{ $iconClass }}"></i>
                <span>Check-in QR</span>
                <span class="staff-badge ml-auto">Staff</span>
            </a>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit"
                    class="logout-btn flex w-full items-center px-6 py-2 text-sm font-medium text-gray-400 hover:text-red-400 transition-all duration-300">
                    <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center mr-3 text-base"></i>
                    Đăng xuất
                </button>
            </form>
        </nav>

        <!-- Bottom Info -->
        <div class="mt-auto p-6 pt-4">
            <div class="bottom-info p-4">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="text-xs text-gray-400">Hệ thống hoạt động</span>
                </div>
                <p class="text-[10px] text-gray-500 leading-relaxed">
                    HoangCinema Admin v2.0<br>
                    Build 2026.07.18
                </p>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="flex flex-col flex-1 overflow-hidden">

        <!-- Header - Nền trắng -->
        <header class="header-cinema flex items-center justify-between px-6 py-4 lg:hidden">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="menu-btn">
                    <svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
                <span class="font-semibold text-gray-800">HoangCinema</span>
            </div>
        </header>

        <!-- Main Content - Nền trắng -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto content-area">
            <div class="container px-6 py-8 mx-auto">

                <!-- Alerts -->
                @if(session('success'))
                    <div class="mb-4 alert-success px-5 py-4 relative flex items-start gap-3" role="alert">
                        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-check text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-green-800 mb-0.5">Thành công</p>
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 alert-error px-5 py-4 relative flex items-start gap-3" role="alert">
                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-xmark text-red-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-red-800 mb-0.5">Lỗi</p>
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @include('components.toast')
</body>

</html>