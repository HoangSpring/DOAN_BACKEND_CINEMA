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
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-100 flex h-screen" x-data="{ sidebarOpen: false }">

    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-20 transition-opacity bg-black opacity-50 lg:hidden"
        @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'"
        class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto transition duration-300 transform bg-gray-900 lg:translate-x-0 lg:static lg:inset-0 text-white">
        <div class="flex items-center justify-center mt-8 px-4">
            <a href="{{ route('home') }}" class="hover:opacity-80 transition-opacity">
                <img src="{{ asset('logo-hoangcinema.svg') }}" alt="HoangCinema"
                    class="h-12 w-auto filter drop-shadow-md">
            </a>
        </div>
        <nav class="mt-10">
            @php
                $navItemClass = "flex items-center px-6 py-2 mt-4 ";
                $activeClass = "text-gray-100 bg-gray-700 bg-opacity-25";
                $inactiveClass = "text-gray-400 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100";
                $iconClass = "w-5 text-center mr-3";
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

            <div class="border-t border-gray-700 mt-4 pt-4 mx-6 text-xs uppercase tracking-wider text-gray-500">
                Vận hành quầy
            </div>

            <a class="{{ $navItemClass }} {{ request()->routeIs('staff.counter') ? $activeClass : $inactiveClass }}"
                href="{{ route('staff.counter') }}">
                <i class="fa-solid fa-cash-register {{ $iconClass }}"></i>
                Bán vé tại quầy (Staff)
            </a>
            <a class="{{ $navItemClass }} {{ request()->routeIs('staff.checkin') ? $activeClass : $inactiveClass }}"
                href="{{ route('staff.checkin') }}">
                <i class="fa-solid fa-qrcode {{ $iconClass }}"></i>
                Check-in QR (Staff)
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex w-full items-center px-6 py-2 mt-4 text-gray-400 hover:bg-gray-700 hover:bg-opacity-25 hover:text-gray-100">
                    <i class="fa-solid fa-arrow-right-from-bracket {{ $iconClass }}"></i>
                    Đăng xuất
                </button>
            </form>
        </nav>
    </div>

    <!-- Content -->
    <div class="flex flex-col flex-1 overflow-hidden">
        <header class="flex items-center justify-between px-6 py-4 bg-white border-b-4 border-indigo-600 lg:hidden">
            <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200">
            <div class="container px-6 py-8 mx-auto">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                        role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
    @include('components.toast')
</body>

</html>