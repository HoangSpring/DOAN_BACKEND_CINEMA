<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff - HoangCinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            body {
                background-color: white !important;
            }
        }
        .print-only {
            display: none;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800">

    <!-- Header / Navbar -->
    <header class="bg-indigo-700 shadow-md no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center gap-3">
                        <a href="{{ route('home') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                            <img src="{{ asset('logo-hoangcinema.svg') }}" alt="HoangCinema" class="h-10 w-auto filter drop-shadow-sm">
                        </a>
                        <span class="text-white text-lg font-bold tracking-wider opacity-80 border-l border-white/20 pl-3">STAFF</span>
                    </div>
                    <div class="hidden sm:ml-10 sm:flex sm:space-x-8">
                        <a href="{{ route('staff.counter') }}" class="border-transparent text-gray-300 hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-lg font-medium transition">
                            Bán vé tại quầy
                        </a>
                        <a href="{{ route('staff.checkin') }}" class="border-transparent text-gray-300 hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-lg font-medium transition">
                            Check-in QR
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-300 hover:text-white font-medium">Đăng xuất</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @include('components.toast')
</body>
</html>
