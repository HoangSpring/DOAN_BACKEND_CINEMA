<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff - HoangCinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* Header Cinema Style */
        .header-staff {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            border-bottom: 2px solid #f5c518;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        /* Nav Link Effects */
        .nav-link {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #f5c518, #ffd700);
            transition: width 0.3s ease;
            border-radius: 2px;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .nav-link.active {
            color: #f5c518;
        }

        /* Logout Button */
        .logout-btn {
            position: relative;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(245, 197, 24, 0.1);
            border: 1px solid rgba(245, 197, 24, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .mobile-menu-btn:hover {
            background: rgba(245, 197, 24, 0.2);
            border-color: #f5c518;
        }

        /* Content Area */
        .content-area {
            background: #f8fafc;
            min-height: calc(100vh - 64px);
        }

        /* Print Styles */
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

        /* Staff Badge */
        .staff-badge {
            background: linear-gradient(135deg, #f5c518, #c9a000);
            color: #111827;
            font-size: 0.65rem;
            padding: 2px 10px;
            border-radius: 9999px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            box-shadow: 0 2px 8px rgba(245, 197, 24, 0.3);
        }

        /* Logo Glow */
        .logo-glow {
            filter: drop-shadow(0 0 8px rgba(245, 197, 24, 0.2));
            transition: all 0.3s ease;
        }

        .logo-glow:hover {
            filter: drop-shadow(0 0 12px rgba(245, 197, 24, 0.4));
        }

        /* Divider */
        .header-divider {
            width: 1px;
            height: 24px;
            background: linear-gradient(180deg, transparent, rgba(245, 197, 24, 0.3), transparent);
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-800">

    <!-- Header / Navbar -->
    <header class="header-staff no-print sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">

                <!-- Left: Logo + Nav -->
                <div class="flex items-center gap-6">
                    <!-- Logo -->
                    <a href="{{ route('home') }}"
                        class="logo-glow flex items-center gap-3 hover:opacity-80 transition-opacity shrink-0">
                        <img src="{{ asset('logo-hoangcinema.svg') }}" alt="HoangCinema" class="h-10 w-auto">
                    </a>

                    <!-- Staff Badge -->
                    <div class="hidden sm:flex items-center gap-3">
                        <div class="header-divider"></div>
                        <span class="staff-badge">Nhân viên</span>
                    </div>

                    <!-- Desktop Nav -->
                    <div class="hidden sm:ml-6 sm:flex sm:items-center sm:gap-1">
                        <a href="{{ route('staff.counter') }}"
                            class="nav-link {{ request()->routeIs('staff.counter') ? 'active' : '' }} text-gray-300 hover:text-white inline-flex items-center px-4 py-2 text-sm font-medium transition rounded-lg">
                            <i class="fa-solid fa-cash-register mr-2 text-cinema-gold/70"></i>
                            Bán vé tại quầy
                        </a>
                        <a href="{{ route('staff.checkin') }}"
                            class="nav-link {{ request()->routeIs('staff.checkin') ? 'active' : '' }} text-gray-300 hover:text-white inline-flex items-center px-4 py-2 text-sm font-medium transition rounded-lg">
                            <i class="fa-solid fa-qrcode mr-2 text-cinema-gold/70"></i>
                            Check-in QR
                        </a>
                    </div>
                </div>

                <!-- Right: User + Logout -->
                <div class="flex items-center gap-4">
                    <!-- Mobile Menu Button -->
                    <button type="button" class="mobile-menu-btn sm:hidden"
                        onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                        <svg class="w-5 h-5 text-cinema-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Desktop Logout -->
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit"
                            class="logout-btn text-gray-300 hover:text-red-400 text-sm font-medium flex items-center gap-2">
                            <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden sm:hidden border-t border-gray-700/50 bg-gray-800/95 backdrop-blur-sm">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('staff.counter') }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('staff.counter') ? 'bg-cinema-gold/10 text-cinema-gold border border-cinema-gold/20' : 'text-gray-300 hover:bg-gray-700/50 hover:text-white' }}">
                    <i class="fa-solid fa-cash-register w-5 text-center"></i>
                    Bán vé tại quầy
                </a>
                <a href="{{ route('staff.checkin') }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('staff.checkin') ? 'bg-cinema-gold/10 text-cinema-gold border border-cinema-gold/20' : 'text-gray-300 hover:bg-gray-700/50 hover:text-white' }}">
                    <i class="fa-solid fa-qrcode w-5 text-center"></i>
                    Check-in QR
                </a>
                <div class="border-t border-gray-700/50 my-2"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 rounded-lg transition">
                        <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center"></i>
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="content-area py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @include('components.toast')
</body>

</html>