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
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 via-40% to-transparent to-70% pointer-events-none">
                </div>

                <div class="relative z-10 flex flex-col justify-end h-full px-5 sm:px-10 md:px-16 pb-10 sm:pb-14 gap-6">

                    {{-- Khối nội dung chính --}}
                    <div class="max-w-2xl flex flex-col items-start gap-4 text-left">
                        <span
                            class="font-body text-primary text-xs sm:text-sm tracking-[0.3em] uppercase drop-shadow-[0_2px_6px_rgba(0,0,0,0.7)]">
                            Hơn 20 phim đang chiếu mỗi tuần trên toàn hệ thống
                        </span>

                        <h1
                            class="font-marquee leading-[1.05] text-4xl sm:text-6xl md:text-7xl tracking-tight text-white drop-shadow-[0_4px_18px_rgba(0,0,0,0.85)]">
                            Đắm Chìm Trong<br>Từng Thước Phim
                        </h1>

                        <p
                            class="font-body text-white/90 max-w-xl leading-relaxed text-sm sm:text-base font-light drop-shadow-[0_2px_8px_rgba(0,0,0,0.7)]">
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
                    <div
                        class="flex flex-wrap gap-x-3 sm:gap-x-4 gap-y-1 font-body text-white/60 text-xs sm:text-sm font-light tracking-wide drop-shadow-[0_2px_6px_rgba(0,0,0,0.7)]">
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
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between mb-12 border-b border-dark-border pb-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-primary/80 mb-3 font-body flex items-center gap-2">
                        <span class="w-8 h-[1px] bg-primary/50"></span>
                        Lịch chiếu rạp
                    </p>
                    <h1 class="text-4xl md:text-5xl font-marquee tracking-tight text-white">
                        Danh Mục <span class="text-primary">Phim</span>
                    </h1>
                </div>
                <div class="flex flex-wrap gap-2 font-body">
                    <a href="{{ route('home', ['status' => 'all']) }}#danh-sach-phim"
                        class="group relative rounded-full px-6 py-2.5 text-sm font-semibold transition-all duration-300 overflow-hidden {{ $status === 'all' ? 'bg-primary text-black shadow-[0_0_20px_rgba(245,197,24,0.3)]' : 'bg-dark-card border-dark-border border text-slate-300 hover:border-primary/50 hover:text-primary' }}">
                        <span class="relative z-10">Tất cả</span>
                    </a>
                    <a href="{{ route('home', ['status' => 'showing']) }}#danh-sach-phim"
                        class="group relative rounded-full px-6 py-2.5 text-sm font-semibold transition-all duration-300 overflow-hidden {{ $status === 'showing' ? 'bg-primary text-black shadow-[0_0_20px_rgba(245,197,24,0.3)]' : 'bg-dark-card border-dark-border border text-slate-300 hover:border-primary/50 hover:text-primary' }}">
                        <span class="relative z-10 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            Đang chiếu
                        </span>
                    </a>
                    <a href="{{ route('home', ['status' => 'coming_soon']) }}#danh-sach-phim"
                        class="group relative rounded-full px-6 py-2.5 text-sm font-semibold transition-all duration-300 overflow-hidden {{ $status === 'coming_soon' ? 'bg-primary text-black shadow-[0_0_20px_rgba(245,197,24,0.3)]' : 'bg-dark-card border-dark-border border text-slate-300 hover:border-primary/50 hover:text-primary' }}">
                        <span class="relative z-10 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Sắp chiếu
                        </span>
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
                    <div class="mb-8 flex items-end justify-between">
                        <div>
                            <h2 class="text-3xl font-marquee tracking-tight text-white mb-2 flex items-center gap-3">
                                {{ $section['title'] }}
                                <span
                                    class="text-sm font-body font-normal text-slate-500 bg-dark-card px-3 py-1 rounded-full border border-dark-border">
                                    {{ count($section['movies']) }} phim
                                </span>
                            </h2>
                            <p class="text-slate-400 font-body text-sm">
                                {{ $section['desc'] }}
                            </p>
                        </div>
                    </div>

                    {{-- GRID --}}
                    <div class="grid gap-8 md:grid-cols-2">
                        @forelse($section['movies'] as $movie)
                            <article x-data="{ expandedDates: false, hovered: false }" @mouseenter="hovered = true"
                                @mouseleave="hovered = false"
                                class="group flex flex-row gap-5 rounded-2xl border border-dark-border bg-dark-card p-5 shadow-2xl transition-all duration-500 hover:border-primary/30 hover:-translate-y-2 hover:shadow-[0_20px_60px_rgba(0,0,0,0.6),0_0_30px_rgba(245,197,24,0.05)] font-body w-full relative overflow-hidden">

                                {{-- Glow effect on hover --}}
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none">
                                </div>

                                {{-- Poster bên trái - TO HƠN + self-start để không bị giãn --}}
                                <a href="{{ route('movies.show', $movie->id) }}"
                                    class="shrink-0 self-start w-52 sm:w-60 md:w-64 lg:w-72 overflow-hidden rounded-xl bg-black aspect-[2/3] shadow-lg relative group/poster">
                                    <img src="{{ $movie->poster_url ?? 'https://picsum.photos/seed/' . $movie->id . '/760/1080' }}"
                                        alt="{{ $movie->title }}"
                                        class="w-full h-full object-cover transition duration-700 group-hover/poster:scale-110">
                                    {{-- Overlay gradient on poster --}}
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover/poster:opacity-100 transition-opacity duration-300">
                                    </div>
                                    {{-- Play icon --}}
                                    <div
                                        class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/poster:opacity-100 transition-all duration-300">
                                        <div
                                            class="w-14 h-14 rounded-full bg-primary/90 flex items-center justify-center backdrop-blur-sm transform scale-50 group-hover/poster:scale-100 transition-transform duration-300">
                                            <svg class="w-6 h-6 text-black ml-1" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>

                                {{-- Thông tin phim bên phải --}}
                                <div class="flex-1 min-w-0 flex flex-col justify-between relative z-10">
                                    <div>
                                        @php
                                            $ageColors = [
                                                'P' => ['badge' => 'bg-emerald-600', 'warnText' => 'text-emerald-300', 'warnBg' => 'bg-emerald-950/40', 'warnBorder' => 'border-emerald-900/50'],
                                                'K' => ['badge' => 'bg-blue-600', 'warnText' => 'text-blue-300', 'warnBg' => 'bg-blue-950/40', 'warnBorder' => 'border-blue-900/50'],
                                                'T13' => ['badge' => 'bg-amber-500', 'warnText' => 'text-amber-300', 'warnBg' => 'bg-amber-950/40', 'warnBorder' => 'border-amber-900/50'],
                                                'T16' => ['badge' => 'bg-orange-500', 'warnText' => 'text-orange-300', 'warnBg' => 'bg-orange-950/40', 'warnBorder' => 'border-orange-900/50'],
                                                'T18' => ['badge' => 'bg-red-600', 'warnText' => 'text-red-300', 'warnBg' => 'bg-red-950/40', 'warnBorder' => 'border-red-900/50'],
                                            ];
                                            $ac = $ageColors[$movie->age_rating] ?? ['badge' => 'bg-slate-600', 'warnText' => 'text-slate-300', 'warnBg' => 'bg-slate-800/40', 'warnBorder' => 'border-slate-700/50'];
                                        @endphp
                                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                                            <a href="{{ route('movies.show', $movie->id) }}">
                                                <h3
                                                    class="text-base sm:text-lg md:text-xl font-marquee text-white uppercase tracking-wide hover:text-primary transition-colors leading-tight line-clamp-2">
                                                    {{ $movie->title }}
                                                </h3>
                                            </a>
                                            @if($movie->age_rating)
                                                <span
                                                    class="{{ $ac['badge'] }} text-white text-[10px] sm:text-xs px-2.5 py-1 rounded-md font-bold uppercase tracking-wider shrink-0 shadow-lg">
                                                    {{ $movie->age_rating }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-400 mb-3">
                                            @if($movie->genre)
                                                <span
                                                    class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-white/5 border border-white/10">
                                                    <svg class="w-3 h-3 text-primary" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                    </svg>
                                                    {{ $movie->genre }}
                                                </span>
                                            @endif
                                            <span
                                                class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-white/5 border border-white/10">
                                                <svg class="w-3 h-3 text-primary" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $movie->duration_minutes }} phút
                                            </span>
                                            @if($movie->country)
                                                <span
                                                    class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-white/5 border border-white/10">
                                                    <svg class="w-3 h-3 text-primary" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $movie->country }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($movie->subtitle_type && $movie->subtitle_type !== 'none')
                                            <div
                                                class="flex items-center gap-1.5 text-xs text-slate-400 mb-3 px-2 py-1 rounded-md bg-white/5 border border-white/10 w-fit">
                                                <svg class="w-3 h-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                </svg>
                                                {{ $movie->subtitle_type === 'dubbed' ? 'Lồng tiếng' : 'Phụ đề' }}
                                            </div>
                                        @endif

                                        @if($movie->age_rating)
                                            @php
                                                $ageWarnings = [
                                                    'P' => 'Phim được phép phổ biến rộng rãi đến mọi lứa tuổi.',
                                                    'K' => 'Phim phổ biến đến khán giả dưới 13 tuổi có người giám hộ đi kèm.',
                                                    'T13' => 'Phim dành cho khán giả từ đủ 13 tuổi (13+).',
                                                    'T16' => 'Phim dành cho khán giả từ đủ 16 tuổi (16+).',
                                                    'T18' => 'Phim dành cho khán giả từ đủ 18 tuổi (18+).',
                                                ];
                                            @endphp
                                            <div
                                                class="flex items-start gap-2 text-[10px] sm:text-xs {{ $ac['warnText'] }} {{ $ac['warnBg'] }} {{ $ac['warnBorder'] }} border rounded-lg p-2.5 mb-3 leading-snug backdrop-blur-sm">
                                                <svg class="w-4 h-4 mt-0.5 shrink-0 opacity-70" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                                <span>{{ $ageWarnings[$movie->age_rating] ?? '' }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Lịch chiếu xếp ngang mượt mà --}}
                                    @if($movie->showtimes->count())
                                        @php
                                            $today = \Carbon\Carbon::today();
                                            $tomorrow = \Carbon\Carbon::tomorrow()->endOfDay();

                                            $filteredShowtimes = $movie->showtimes->filter(function ($s) use ($today, $tomorrow) {
                                                return $s->start_time->between($today, $tomorrow);
                                            });

                                            $groupedByDate = $filteredShowtimes
                                                ->sortBy('start_time')
                                                ->groupBy(fn($s) => $s->start_time->locale('vi')->isoFormat('dddd, DD/MM/YYYY'));
                                        @endphp

                                        @if($groupedByDate->isNotEmpty())
                                            <div class="space-y-3 mt-auto">
                                                @foreach($groupedByDate as $dateLabel => $showtimesOfDate)
                                                    <div class="border border-white/10 rounded-xl overflow-hidden bg-black/20 backdrop-blur-sm">
                                                        <div
                                                            class="flex items-center justify-between px-4 py-2 bg-white/5 text-[11px] sm:text-xs font-semibold text-slate-300 border-b border-white/5">
                                                            <span class="capitalize flex items-center gap-2">
                                                                <svg class="w-3 h-3 text-primary" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                                {{ $dateLabel }}
                                                            </span>
                                                        </div>

                                                        {{-- Toàn bộ các phòng và giờ chiếu sẽ flex ngang hàng --}}
                                                        <div class="p-4 flex flex-wrap gap-x-6 gap-y-3 items-start">
                                                            @foreach($showtimesOfDate->groupBy(fn($s) => $s->room->name) as $roomName => $showtimesOfRoom)
                                                                <div class="flex flex-col gap-2 shrink-0">
                                                                    <p
                                                                        class="text-[10px] font-black uppercase tracking-wider text-primary flex items-center gap-1">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                                        </svg>
                                                                        {{ $roomName }}
                                                                    </p>
                                                                    <div class="flex flex-wrap gap-2">
                                                                        @foreach($showtimesOfRoom as $showtime)
                                                                            @if(now('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s') > $showtime->start_time->format('Y-m-d H:i:s'))
                                                                                <span
                                                                                    class="border border-slate-700/50 text-slate-600 bg-slate-800/30 text-xs font-bold px-3 py-1.5 rounded-lg cursor-not-allowed opacity-40 line-through">
                                                                                    {{ $showtime->start_time->format('H:i') }}
                                                                                </span>
                                                                            @else
                                                                                <a href="{{ url('/showtimes/' . $showtime->id . '/seats') }}"
                                                                                    class="group/time relative border border-slate-600 hover:border-primary hover:bg-primary/10 text-slate-300 hover:text-primary text-xs font-bold px-3 py-1.5 rounded-lg transition-all duration-300 overflow-hidden">
                                                                                    <span
                                                                                        class="relative z-10">{{ $showtime->start_time->format('H:i') }}</span>
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
                                            <div
                                                class="rounded-xl border border-dark-border bg-black/25 p-4 text-slate-500 text-xs font-light mt-auto flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Chưa có lịch chiếu trong hôm nay và ngày mai.
                                            </div>
                                        @endif

                                        <a href="{{ route('movies.show', $movie->id) }}#showtimes"
                                            class="mt-3 text-xs font-bold text-primary hover:text-yellow-300 inline-flex items-center gap-1 group/link">
                                            Xem thêm lịch chiếu
                                            <svg class="w-3 h-3 transition-transform group-hover/link:translate-x-1" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    @else
                                        <div
                                            class="rounded-xl border border-dark-border bg-black/25 p-4 text-slate-500 text-xs font-light mt-auto flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Chưa có lịch chiếu sẵn sàng.
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div
                                class="rounded-3xl border border-dark-border bg-dark-card/50 p-12 text-center text-slate-400 col-span-full flex flex-col items-center gap-4">
                                <svg class="w-16 h-16 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                                </svg>
                                <p class="text-lg">Chưa có {{ strtolower($section['title']) }} nào.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ============ GOOGLE MAPS ============ --}}
        <section class="border-t border-dark-border bg-dark-card/40 mt-16 relative overflow-hidden">
            {{-- Decorative elements --}}
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>

            <div class="container mx-auto px-4 py-16 relative z-10">
                <div class="mb-10 text-center md:text-left">
                    <p
                        class="text-xs uppercase tracking-[0.35em] text-primary/80 mb-3 font-body flex items-center gap-2 justify-center md:justify-start">
                        <span class="w-8 h-[1px] bg-primary/50"></span>
                        Ghé thăm chúng tôi
                    </p>
                    <h2 class="text-3xl md:text-4xl font-marquee tracking-tight text-white">
                        Vị Trí <span class="text-primary">Rạp Chiếu</span>
                    </h2>
                </div>

                <div class="grid gap-8 lg:grid-cols-12">
                    {{-- Thông tin liên hệ --}}
                    <div class="lg:col-span-4 flex flex-col gap-6 font-body justify-center">
                        <div
                            class="group flex items-start gap-4 p-4 rounded-2xl bg-dark-card/50 border border-dark-border hover:border-primary/30 transition-all duration-300">
                            <div
                                class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center shrink-0 group-hover:bg-primary/20 transition-colors">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-semibold mb-1">Địa chỉ rạp</p>
                                <p class="text-slate-400 text-sm leading-relaxed">
                                    176 Trần Phú, phường Phước Vĩnh, TP. Huế
                                </p>
                            </div>
                        </div>

                        <div
                            class="group flex items-start gap-4 p-4 rounded-2xl bg-dark-card/50 border border-dark-border hover:border-primary/30 transition-all duration-300">
                            <div
                                class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center shrink-0 group-hover:bg-primary/20 transition-colors">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-semibold mb-1">Hotline đặt vé</p>
                                <p class="text-slate-400 text-sm font-mono">1900 1234</p>
                            </div>
                        </div>

                        <div
                            class="group flex items-start gap-4 p-4 rounded-2xl bg-dark-card/50 border border-dark-border hover:border-primary/30 transition-all duration-300">
                            <div
                                class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center shrink-0 group-hover:bg-primary/20 transition-colors">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-semibold mb-1">Giờ mở cửa</p>
                                <p class="text-slate-400 text-sm">08:00 — 23:30 (Tất cả các ngày trong tuần)</p>
                            </div>
                        </div>

                        <a href="https://www.google.com/maps/dir/?api=1&destination=16.4423,107.5878" target="_blank"
                            rel="noopener noreferrer"
                            class="group inline-flex items-center gap-2 px-6 py-3 bg-dark-card border border-primary/30 text-primary rounded-full hover:bg-primary hover:text-black transition-all duration-300 font-semibold text-sm self-start">
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-45" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Chỉ đường
                        </a>
                    </div>

                    {{-- Bản đồ --}}
                    <div class="lg:col-span-8">
                        <div
                            class="rounded-3xl overflow-hidden border border-dark-border h-[400px] md:h-[500px] min-h-[400px] md:min-h-[500px] relative shadow-2xl group">
                            <div
                                class="absolute inset-0 bg-primary/5 z-10 pointer-events-none group-hover:bg-transparent transition-colors duration-500">
                            </div>
                            <iframe src="https://maps.google.com/maps?q=Trường+Đại+học+Phú+Xuân+Huế&output=embed"
                                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                class="absolute inset-0 w-full h-full grayscale group-hover:grayscale-0 transition-all duration-700">
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