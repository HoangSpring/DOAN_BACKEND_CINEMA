<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff - Cinema</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-white text-2xl font-bold tracking-wider">STAFF PORTAL</span>
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

    @include('components.toast')
</body>
</html>
