@extends('layouts.customer')

@section('content')

    <div class="min-h-screen bg-dark text-white font-body">

        {{-- ============ HERO: chuyển cảnh trailer theo phim ============ --}}
        @if($featuredMovies->count() > 0)
            <section x-data="cinemaTrailerHero(@js($featuredMovies->map(fn($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'poster_url' => $m->poster_url ?? 'https://picsum.photos/seed/movie-' . $m->id . '/1600/1000',
                'trailer_url' => $m->trailer_url ?? null,
            ])))" class="hero-lumora relative w-full h-screen overflow-hidden bg-black select-none">

                {{-- Background Video / Poster --}}
                <template x-for="(movie, i) in movies" :key="movie.id">
                    <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                        :class="active === i ? 'opacity-100' : 'opacity-0'">
                        <template x-if="movie.trailer_url">
                            <video class="w-full h-full object-cover" autoplay muted loop playsinline
                                :poster="movie.poster_url">
                                <source :src="movie.trailer_url" type="video/mp4">
                            </video>
                        </template>
                        <template x-if="!movie.trailer_url">
                            <img class="w-full h-full object-cover" :src="movie.poster_url" :alt="movie.title">
                        </template>
                    </div>
                </template>

                {{-- Lớp phủ --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 via-40% to-transparent to-70% pointer-events-none"></div>

                <div class="relative z-10 flex flex-col justify-end h-full px-5 sm:px-10 md:px-16 pb-10 sm:pb-14 gap-6">

                    {{-- Khối nội dung chính --}}
                    <div class="max-w-2xl flex flex-col items-start gap-4 text-left">
                        <span class="font-body text-primary text-xs sm:text-sm tracking-[0.3em] uppercase drop-shadow-[0_2px_6px_rgba(0,0,0,0.7)]">
                            Hơn 20 phim đang chiếu mỗi tuần trên toàn hệ thống
                        </span>

                        <h1 class="font-marquee leading-[1.05] text-4xl sm:text-6xl md:text-7xl tracking-tight text-white drop-shadow-[0_4px_18px_rgba(0,0,0,0.85)]">
                            Đắm Chìm Trong<br>Từng Thước Phim
                        </h1>

                        <p class="font-body text-white/90 max-w-xl leading-relaxed text-sm sm:text-base font-light drop-shadow-[0_2px_8px_rgba(0,0,0,0.7)]">
                            Tạm gác lại thế giới ồn ào ngoài kia — bước vào bóng tối rạp chiếu,<br>
                            nơi mỗi câu chuyện được kể trọn vẹn trên màn ảnh rộng.
                        </p>

                        <a href="#danh-sach-phim" class="btn-primary mt-1">
                            Đặt Vé Ngay
                        </a>
                    </div>

                    {{-- Thanh điều hướng chọn phim --}}
                    <div class="flex flex-wrap gap-x-6 gap-y-2 font-body">
                        <template x-for="(movie, i) in movies" :key="movie.id">
                            <button @click="switchTo(i)"
                                class="text-xs sm:text-sm pb-1 border-b transition-all duration-300 drop-shadow-[0_2px_6px_rgba(0,0,0,0.7)]"
                                :class="active === i
                                            ? 'text-primary border-primary opacity-100 font-medium'
                                            : 'text-white/70 border-transparent hover:text-white'"
                                x-text="movie.title"></button>
                        </template>
                    </div>

                    {{-- Dòng thông số --}}
                    <div class="flex flex-wrap gap-x-3 sm:gap-x-4 gap-y-1 font-body text-white/60 text-xs sm:text-sm font-light tracking-wide drop-shadow-[0_2px_6px_rgba(0,0,0,0.7)]">
                        <span>20+ Phim Đang Chiếu</span>
                        <span class="hidden sm:inline text-white/25">|</span>
                        <span>5 Phòng Chiếu Hiện Đại</span>
                        <span class="hidden sm:inline text-white/25">|</span>
                        <span>4.8 Đánh Giá Khách Hàng</span>
                        <span class="hidden sm:inline text-white/25">|</span>
                        <span>Trải Nghiệm Điện Ảnh Đỉnh Cao</span>
                    </div>
                </div>
            </section>
        @endif
        {{-- ============ HẾT HERO ============ --}}

        {{-- CONTAINER --}}
        <div id="danh-sach-phim" class="w-full max-w-7xl mx-auto px-6 sm:px-12 py-16">
            
            {{-- FILTER BUTTONS HEADER --}}
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between mb-12 border-b border-dark-border pb-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-primary/80 mb-3 font-body">Lịch chiếu rạp</p>
                    <h1 class="text-4xl md:text-5xl font-marquee tracking-tight text-white">
                        Danh Mục Phim
                    </h1>
                </div>
                <div class="flex flex-wrap gap-2 font-body">
                    <a href="{{ route('home', ['status' => 'all']) }}#danh-sach-phim"
                        class="rounded-full px-5 py-2.5 text-sm font-semibold transition-all duration-300 {{ $status === 'all' ? 'bg-primary text-black shadow-glow-primary' : 'bg-dark-card border-dark-border border text-slate-300 hover:bg-slate-800' }}">
                        Tất cả
                    </a>
                    <a href="{{ route('home', ['status' => 'showing']) }}#danh-sach-phim"
                        class="rounded-full px-5 py-2.5 text-sm font-semibold transition-all duration-300 {{ $status === 'showing' ? 'bg-primary text-black shadow-glow-primary' : 'bg-dark-card border-dark-border border text-slate-300 hover:bg-slate-800' }}">
                        Đang chiếu
                    </a>
                    <a href="{{ route('home', ['status' => 'coming_soon']) }}#danh-sach-phim"
                        class="rounded-full px-5 py-2.5 text-sm font-semibold transition-all duration-300 {{ $status === 'coming_soon' ? 'bg-primary text-black shadow-glow-primary' : 'bg-dark-card border-dark-border border text-slate-300 hover:bg-slate-800' }}">
                        Sắp chiếu
                    </a>
                </div>
            </div>

            @php
                $sections = [];
                if ($status === 'all' || $status === 'showing') {
                    $sections[] = [
                        'title' => 'Phim Đang Chiếu', 
                        'desc' => 'Duyệt nhanh phim hot, xem lịch chiếu và đặt vé ngay.', 
                        'movies' => $showingMovies
                    ];
                }
                if ($status === 'all' || $status === 'coming_soon') {
                    $sections[] = [
                        'title' => 'Phim Sắp Chiếu', 
                        'desc' => 'Những siêu phẩm điện ảnh sắp đổ bộ phòng vé.', 
                        'movies' => $comingSoonMovies
                    ];
                }
            @endphp

            @foreach($sections as $section)
                <div class="mb-8 {{ $loop->index > 0 ? 'mt-24 pt-8 border-t border-dark-border' : '' }}">
                    <div class="mb-8">
                        <h2 class="text-3xl font-marquee tracking-tight text-white mb-2">
                            {{ $section['title'] }}
                        </h2>
                        <p class="text-slate-400 font-body text-sm">
                            {{ $section['desc'] }}
                        </p>
                    </div>

                    {{-- GRID --}}
                    <div class="grid gap-8 md:grid-cols-2">
                        @forelse($section['movies'] as $movie)
                            <article x-data="{ expandedDates: false }"
                                class="group flex flex-row gap-5 rounded-2xl border border-dark-border bg-dark-card p-5 shadow-2xl transition-all duration-300 hover:border-white/20 hover:-translate-y-1 hover:shadow-[0_12px_40px_rgba(0,0,0,0.5)] font-body w-full">

                                {{-- Poster bên trái --}}
                                <a href="{{ route('movies.show', $movie->id) }}" class="shrink-0 w-48 sm:w-56 md:w-60 overflow-hidden rounded-xl bg-black aspect-[2/3] shadow-lg">
                                    <img src="{{ $movie->poster_url ?? 'https://picsum.photos/seed/' . $movie->id . '/760/1080' }}"
                                        alt="{{ $movie->title }}"
                                        class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                                </a>

                                {{-- Thông tin phim bên phải --}}
                                <div class="flex-1 min-w-0 flex flex-col justify-between">
                                    <div>
                                        @php
                                            $ageColors = [
                                                'P'   => ['badge' => 'bg-emerald-600', 'warnText' => 'text-emerald-300', 'warnBg' => 'bg-emerald-950/40', 'warnBorder' => 'border-emerald-900/50'],
                                                'K'   => ['badge' => 'bg-blue-600', 'warnText' => 'text-blue-300', 'warnBg' => 'bg-blue-950/40', 'warnBorder' => 'border-blue-900/50'],
                                                'T13' => ['badge' => 'bg-amber-500', 'warnText' => 'text-amber-300', 'warnBg' => 'bg-amber-950/40', 'warnBorder' => 'border-amber-900/50'],
                                                'T16' => ['badge' => 'bg-orange-500', 'warnText' => 'text-orange-300', 'warnBg' => 'bg-orange-950/40', 'warnBorder' => 'border-orange-900/50'],
                                                'T18' => ['badge' => 'bg-red-600', 'warnText' => 'text-red-300', 'warnBg' => 'bg-red-950/40', 'warnBorder' => 'border-red-900/50'],
                                            ];
                                            $ac = $ageColors[$movie->age_rating] ?? ['badge' => 'bg-slate-600', 'warnText' => 'text-slate-300', 'warnBg' => 'bg-slate-800/40', 'warnBorder' => 'border-slate-700/50'];
                                        @endphp
                                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                                            <a href="{{ route('movies.show', $movie->id) }}">
                                                <h3 class="text-base sm:text-lg md:text-xl font-marquee text-white uppercase tracking-wide hover:text-primary transition-colors leading-tight line-clamp-2">
                                                    {{ $movie->title }}
                                                </h3>
                                            </a>
                                            @if($movie->age_rating)
                                                <span class="{{ $ac['badge'] }} text-white text-[10px] sm:text-xs px-2 py-0.5 rounded font-bold uppercase tracking-wider shrink-0">
                                                    {{ $movie->age_rating }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400 mb-2">
                                            @if($movie->genre)
                                                <span class="flex items-center gap-1.5">
                                                    <i class="fas fa-tag text-primary text-[10px]"></i> {{ $movie->genre }}
                                                </span>
                                            @endif
                                            <span class="flex items-center gap-1.5">
                                                <i class="fas fa-clock text-primary text-[10px]"></i> {{ $movie->duration_minutes }} phút
                                            </span>
                                            @if($movie->country)
                                                <span class="flex items-center gap-1.5">
                                                    <i class="fas fa-globe text-primary text-[10px]"></i> {{ $movie->country }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($movie->subtitle_type && $movie->subtitle_type !== 'none')
                                            <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-2">
                                                <i class="fas fa-closed-captioning text-primary text-[10px]"></i>
                                                {{ $movie->subtitle_type === 'dubbed' ? 'Lồng tiếng' : 'Phụ đề' }}
                                            </div>
                                        @endif

                                        @if($movie->age_rating)
                                            @php
                                                $ageWarnings = [
                                                    'P'   => 'P: Phim được phép phổ biến rộng rãi đến mọi lứa tuổi.',
                                                    'K'   => 'K: Phim phổ biến đến khán giả dưới 13 tuổi có người giám hộ đi kèm.',
                                                    'T13' => 'T13: Phim dành cho khán giả từ đủ 13 tuổi (13+).',
                                                    'T16' => 'T16: Phim dành cho khán giả từ đủ 16 tuổi (16+).',
                                                    'T18' => 'T18: Phim dành cho khán giả từ đủ 18 tuổi (18+).',
                                                ];
                                            @endphp
                                            <div class="flex items-start gap-1.5 text-[10px] sm:text-xs {{ $ac['warnText'] }} {{ $ac['warnBg'] }} {{ $ac['warnBorder'] }} border rounded-lg p-2 mb-3 leading-snug">
                                                <i class="fas fa-user-shield mt-0.5 shrink-0"></i>
                                                <span>{{ $ageWarnings[$movie->age_rating] ?? '' }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Lịch chiếu xếp ngang mượt mà --}}
                                    @if($movie->showtimes->count())
                                        @php
                                            $today = \Carbon\Carbon::today();
                                            $tomorrow = \Carbon\Carbon::tomorrow()->endOfDay();
                                            
                                            $filteredShowtimes = $movie->showtimes->filter(function($s) use ($today, $tomorrow) {
                                                return $s->start_time->between($today, $tomorrow);
                                            });

                                            $groupedByDate = $filteredShowtimes
                                                ->sortBy('start_time')
                                                ->groupBy(fn($s) => $s->start_time->locale('vi')->isoFormat('dddd, DD/MM/YYYY'));
                                        @endphp

                                        @if($groupedByDate->isNotEmpty())
                                            <div class="space-y-3 mt-auto">
                                                @foreach($groupedByDate as $dateLabel => $showtimesOfDate)
                                                    <div class="border border-white/5 rounded-xl overflow-hidden">
                                                        <div class="flex items-center justify-between px-3 py-1.5 bg-black/30 text-[11px] sm:text-xs font-semibold text-slate-200">
                                                            <span class="capitalize">{{ $dateLabel }}</span>
                                                        </div>
                                                        
                                                        {{-- Toàn bộ các phòng và giờ chiếu sẽ flex ngang hàng --}}
                                                        <div class="p-3 flex flex-wrap gap-x-6 gap-y-3 items-start">
                                                            @foreach($showtimesOfDate->groupBy(fn($s) => $s->room->name) as $roomName => $showtimesOfRoom)
                                                                <div class="flex flex-col gap-1.5 shrink-0">
                                                                    <p class="text-[10px] font-black uppercase tracking-wider text-primary">{{ $roomName }}</p>
                                                                    <div class="flex flex-wrap gap-1.5">
                                                                        @foreach($showtimesOfRoom as $showtime)
                                                                            @if(now('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s') > $showtime->start_time->format('Y-m-d H:i:s'))
                                                                                <span class="border border-slate-700/50 text-slate-500 bg-slate-800/30 text-xs font-bold px-2.5 py-1 rounded-lg cursor-not-allowed opacity-30">
                                                                                    {{ $showtime->start_time->format('H:i') }}
                                                                                </span>
                                                                            @else
                                                                                <a href="{{ url('/showtimes/' . $showtime->id . '/seats') }}"
                                                                                    class="border border-slate-700 hover:border-primary hover:text-primary text-slate-300 text-xs font-bold px-2.5 py-1 rounded-lg transition-colors">
                                                                                    {{ $showtime->start_time->format('H:i') }}
                                                                                </a>
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="rounded-xl border border-dark-border bg-black/25 p-3 text-slate-500 text-xs font-light mt-auto">
                                                Chưa có lịch chiếu trong hôm nay và ngày mai.
                                            </div>
                                        @endif

                                        <a href="{{ route('movies.show', $movie->id) }}#showtimes"
                                            class="mt-2 text-xs font-bold text-primary hover:underline inline-flex items-center gap-1">
                                            Xem thêm lịch chiếu
                                        </a>
                                    @else
                                        <div class="rounded-xl border border-dark-border bg-black/25 p-3 text-slate-500 text-xs font-light mt-auto">
                                            Chưa có lịch chiếu sẵn sàng.
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="rounded-3xl border border-dark-border bg-dark-card/50 p-12 text-center text-slate-400 col-span-full">
                                Chưa có {{ strtolower($section['title']) }} nào.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ============ GOOGLE MAPS ============ --}}
        <section class="border-t border-dark-border bg-dark-card/40 mt-16">
            <div class="container mx-auto px-4 py-16">
                <div class="mb-10 text-center md:text-left">
                    <p class="text-xs uppercase tracking-[0.35em] text-primary/80 mb-3 font-body">Ghé thăm chúng tôi</p>
                    <h2 class="text-3xl md:text-4xl font-marquee tracking-tight text-white">Vị Trí Rạp Chiếu</h2>
                </div>

                <div class="grid gap-8 lg:grid-cols-12">
                    {{-- Thông tin liên hệ --}}
                    <div class="lg:col-span-4 flex flex-col gap-6 font-body justify-center">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary/10 border border-primary/30 flex items-center justify-center shrink-0">
                                <i class="fas fa-location-dot text-primary"></i>
                            </div>
                            <div>
                                <p class="text-white font-semibold mb-1">Địa chỉ rạp</p>
                                <p class="text-slate-400 text-sm leading-relaxed">
                                   176 Trần Phú, phường Phước Vĩnh, TP. Huế
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary/10 border border-primary/30 flex items-center justify-center shrink-0">
                                <i class="fas fa-phone text-primary"></i>
                            </div>
                            <div>
                                <p class="text-white font-semibold mb-1">Hotline đặt vé</p>
                                <p class="text-slate-400 text-sm">1900 1234</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary/10 border border-primary/30 flex items-center justify-center shrink-0">
                                <i class="fas fa-clock text-primary"></i>
                            </div>
                            <div>
                                <p class="text-white font-semibold mb-1">Giờ mở cửa</p>
                                <p class="text-slate-400 text-sm">08:00 — 23:30 (Tất cả các ngày trong tuần)</p>
                            </div>
                        </div>

                        <a href="https://www.google.com/maps/dir/?api=1&destination=16.4423,107.5878"
                           target="_blank" rel="noopener noreferrer"
                           class="btn-secondary self-start mt-2">
                            <i class="fas fa-diamond-turn-right mr-2"></i> Chỉ đường
                        </a>
                    </div>

                    {{-- Bản đồ --}}
                    <div class="lg:col-span-8">
                        <div class="rounded-3xl overflow-hidden border border-dark-border h-[400px] md:h-[500px] min-h-[400px] md:min-h-[500px] relative shadow-2xl">
                            <iframe
                                src="https://maps.google.com/maps?q=Trường+Đại+học+Phú+Xuân+Huế&output=embed"
                                width="100%"
                                height="100%"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                class="absolute inset-0 w-full h-full">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>  
        {{-- ============ HẾT GOOGLE MAPS ============ --}}

    </div>

    <script>
        function cinemaTrailerHero(movies) {
            return {
                movies: movies,
                active: 0,
                transitioning: false,

                switchTo(i) {
                    if (i === this.active || this.transitioning) return;
                    this.transitioning = true;
                    this.active = i;
                    setTimeout(() => { this.transitioning = false; }, 1000);
                },
            };
        }
    </script>
@endsection