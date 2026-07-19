@extends('layouts.customer')

@section('content')
    <style>
        /* ===== CINEMA LOGIN THEME ===== */
        :root {
            --cinema-gold: #f5c518;
            --cinema-gold-light: #ffd700;
            --cinema-gold-dark: #c9a000;
            --cinema-black: #0a0a0a;
            --cinema-dark: #141414;
            --cinema-card: #1a1a1a;
            --cinema-border: #2a2a2a;
        }

        /* Background với gradient động - mô phỏng ánh sáng rạp phim */
        .cinema-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(245, 197, 24, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 50%, rgba(245, 197, 24, 0.05) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 100%, rgba(245, 197, 24, 0.03) 0%, transparent 50%),
                linear-gradient(180deg, #0a0a0a 0%, #141414 50%, #0a0a0a 100%);
            z-index: -2;
        }

        /* Hiệu ứng bokeh - ánh sáng mờ ảo */
        .bokeh {
            position: fixed;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.3;
            animation: float 20s infinite ease-in-out;
            z-index: -1;
        }

        .bokeh-1 {
            width: 300px;
            height: 300px;
            background: rgba(245, 197, 24, 0.15);
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .bokeh-2 {
            width: 200px;
            height: 200px;
            background: rgba(245, 197, 24, 0.1);
            top: 60%;
            right: 15%;
            animation-delay: -5s;
        }

        .bokeh-3 {
            width: 250px;
            height: 250px;
            background: rgba(255, 215, 0, 0.08);
            bottom: 20%;
            left: 30%;
            animation-delay: -10s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -30px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }
        }

        /* Particles - hạt sáng bay lơ lửng */
        .particle {
            position: fixed;
            width: 4px;
            height: 4px;
            background: rgba(245, 197, 24, 0.6);
            border-radius: 50%;
            animation: particle-float 15s infinite linear;
            z-index: -1;
        }

        @keyframes particle-float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-10vh) rotate(720deg);
                opacity: 0;
            }
        }

        /* Card glassmorphism với viền phát sáng */
        .cinema-card {
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(245, 197, 24, 0.2);
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                0 0 30px rgba(245, 197, 24, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
            position: relative;
            overflow: hidden;
        }

        /* Viền phát sáng động */
        .cinema-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, transparent, rgba(245, 197, 24, 0.3), transparent, rgba(245, 197, 24, 0.3), transparent);
            background-size: 400% 400%;
            animation: border-glow 8s ease infinite;
            border-radius: inherit;
            z-index: -1;
            opacity: 0.5;
        }

        @keyframes border-glow {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Tiêu đề với hiệu ứng chữ vàng */
        .cinema-title {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: linear-gradient(135deg, #f5c518 0%, #ffd700 50%, #f5c518 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(245, 197, 24, 0.3);
            letter-spacing: 2px;
            position: relative;
        }

        .cinema-title::after {
            content: '🎬';
            position: absolute;
            right: -40px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.6em;
            -webkit-text-fill-color: #f5c518;
            animation: camera-shake 3s ease-in-out infinite;
        }

        @keyframes camera-shake {

            0%,
            100% {
                transform: translateY(-50%) rotate(0deg);
            }

            25% {
                transform: translateY(-50%) rotate(-5deg);
            }

            75% {
                transform: translateY(-50%) rotate(5deg);
            }
        }

        /* Input fields với icon điện ảnh */
        .cinema-input-group {
            position: relative;
        }

        .cinema-input {
            background: rgba(10, 10, 10, 0.6);
            border: 1px solid rgba(245, 197, 24, 0.15);
            color: #fff;
            transition: all 0.3s ease;
            padding-left: 3rem;
        }

        .cinema-input:focus {
            border-color: rgba(245, 197, 24, 0.5);
            box-shadow: 0 0 20px rgba(245, 197, 24, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.05);
            background: rgba(10, 10, 10, 0.8);
        }

        .cinema-input::placeholder {
            color: rgba(148, 163, 184, 0.5);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(245, 197, 24, 0.5);
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .cinema-input:focus+.input-icon,
        .cinema-input-group:focus-within .input-icon {
            color: var(--cinema-gold);
        }

        /* Button gradient vàng với hiệu ứng shine */
        .cinema-btn {
            background: linear-gradient(135deg, #f5c518 0%, #e6b800 50%, #f5c518 100%);
            background-size: 200% 200%;
            color: #0a0a0a;
            font-weight: 700;
            letter-spacing: 1px;
            border: none;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(245, 197, 24, 0.3);
        }

        .cinema-btn:hover {
            background-position: 100% 0;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 197, 24, 0.4);
        }

        .cinema-btn::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(to right,
                    transparent 0%,
                    rgba(255, 255, 255, 0.3) 50%,
                    transparent 100%);
            transform: rotate(30deg) translateX(-100%);
            transition: transform 0.6s ease;
        }

        .cinema-btn:hover::after {
            transform: rotate(30deg) translateX(100%);
        }

        /* Link vàng */
        .cinema-link {
            color: var(--cinema-gold);
            position: relative;
            text-decoration: none;
            font-weight: 600;
        }

        .cinema-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--cinema-gold), var(--cinema-gold-light));
            transition: width 0.3s ease;
        }

        .cinema-link:hover::after {
            width: 100%;
        }

        /* Error message với style rạp phim */
        .cinema-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            backdrop-filter: blur(10px);
        }

        /* Divider với ánh sáng */
        .cinema-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(245, 197, 24, 0.3), transparent);
            margin: 1.5rem 0;
        }

        /* Label style */
        .cinema-label {
            color: rgba(203, 213, 225, 0.9);
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cinema-label::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 4px;
            background: var(--cinema-gold);
            border-radius: 50%;
        }
    </style>

    <!-- Background Effects -->
    <div class="cinema-bg"></div>
    <div class="bokeh bokeh-1"></div>
    <div class="bokeh bokeh-2"></div>
    <div class="bokeh bokeh-3"></div>

    <!-- Particles -->
    <div class="particle" style="left: 10%; animation-duration: 12s; animation-delay: 0s;"></div>
    <div class="particle" style="left: 25%; animation-duration: 15s; animation-delay: 2s; width: 3px; height: 3px;"></div>
    <div class="particle" style="left: 40%; animation-duration: 18s; animation-delay: 4s;"></div>
    <div class="particle" style="left: 55%; animation-duration: 14s; animation-delay: 1s; width: 5px; height: 5px;"></div>
    <div class="particle" style="left: 70%; animation-duration: 16s; animation-delay: 3s;"></div>
    <div class="particle" style="left: 85%; animation-duration: 13s; animation-delay: 5s; width: 2px; height: 2px;"></div>
    <div class="particle" style="left: 15%; animation-duration: 17s; animation-delay: 6s;"></div>
    <div class="particle" style="left: 90%; animation-duration: 11s; animation-delay: 7s; width: 4px; height: 4px;"></div>

    <div class="container mx-auto px-4 py-16 flex justify-center items-center min-h-screen font-body relative z-10">
        <div class="w-full max-w-md cinema-card rounded-2xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="cinema-title text-4xl font-bold mb-3">ĐĂNG NHẬP</div>
                <p class="text-slate-400 text-sm tracking-wide">Vui lòng đăng nhập để đặt vé xem phim</p>
                <div class="cinema-divider mt-4"></div>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="cinema-error px-4 py-3 rounded-lg mb-6">
                    <ul class="list-disc list-inside text-sm text-red-400">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ url('/login') }}" class="space-y-6">
                @csrf

                <!-- Email Field -->
                <div>
                    <label for="email" class="cinema-label mb-2">Email</label>
                    <div class="cinema-input-group">
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="cinema-input w-full rounded-lg px-4 py-3 focus:outline-none transition-all"
                            placeholder="Nhập email của bạn">
                        <span class="input-icon">✉️</span>
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="cinema-label mb-2">Mật khẩu</label>
                    <div class="cinema-input-group">
                        <input type="password" name="password" id="password" required
                            class="cinema-input w-full rounded-lg px-4 py-3 focus:outline-none transition-all"
                            placeholder="••••••••">
                        <span class="input-icon">🎟️</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="cinema-btn w-full flex justify-center items-center gap-2 py-3 rounded-lg text-base cursor-pointer">
                    <span>🎬</span>
                    <span>ĐĂNG NHẬP</span>
                    <span>→</span>
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <div class="cinema-divider mb-4"></div>
                <p class="text-sm text-slate-400">
                    Chưa có tài khoản?
                    <a href="{{ url('/register') }}" class="cinema-link">Đăng ký ngay</a>
                </p>
                <p class="text-xs text-slate-600 mt-2 italic">"Mỗi bộ phim là một cuộc phiêu lưu mới"</p>
            </div>
        </div>
    </div>

    <!-- Script để tạo thêm particles động -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tạo thêm particles ngẫu nhiên
            for (let i = 0; i < 5; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDuration = (10 + Math.random() * 10) + 's';
                particle.style.animationDelay = Math.random() * 5 + 's';
                particle.style.width = (2 + Math.random() * 4) + 'px';
                particle.style.height = particle.style.width;
                document.body.appendChild(particle);
            }
        });
    </script>
@endsection