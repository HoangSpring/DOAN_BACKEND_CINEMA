@extends('layouts.customer')

@section('content')
    <style>
        .font-marquee {
            font-family: 'Bebas Neue', 'Oswald', sans-serif;
            letter-spacing: 0.04em;
        }

        .hero-lumora {
            font-family: 'Instrument Serif', serif;
        }

        .hero-lumora .font-body {
            font-family: system-ui, sans-serif;
        }

        .liquid-glass {
            background: rgba(255, 255, 255, 0.01);
            background-blend-mode: luminosity;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: none;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .liquid-glass::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1.4px;
            background: linear-gradient(180deg,
                    rgba(255, 255, 255, 0.45) 0%, rgba(255, 255, 255, 0.15) 20%,
                    rgba(255, 255, 255, 0) 40%, rgba(255, 255, 255, 0) 60%,
                    rgba(255, 255, 255, 0.15) 80%, rgba(255, 255, 255, 0.45) 100%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }
    </style>

    <div
        class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.14),_transparent_22%),linear-gradient(180deg,#020617_0%,#020617_36%,#070f2c_100%)] text-white">

        {{-- ============ HERO: chuyển cảnh trailer theo phim ============ --}}
        {{--
        Dùng 4 phim đầu trong $movies (danh sách đang hiển thị theo bộ lọc status).
        Phim nào chưa có trailer_url sẽ tự hiện poster thay video (không lỗi).
        Muốn hero luôn lấy đúng phim "đang chiếu" bất kể đang lọc tab nào, có thể sau
        này đổi $movies->take(4) thành 1 biến $featuredMovies riêng truyền từ Controller.

        BỐ CỤC MỚI (Cách A): toàn bộ chữ dời xuống góc dưới-trái, căn trái, chỉ phủ tối
        khoảng 1/3 dưới màn hình. Phần trên/giữa khung hình luôn sáng rõ, không còn phụ
        thuộc vào trailer đang chiếu cảnh gì ở giữa khung hình (logo hãng phim, tiêu đề
        phim...) vì chữ web không còn nằm ở vùng đó nữa.
        --}}
        @if($movies->count() > 0)
            <section x-data="cinemaTrailerHero(@js($movies->take(4)->map(fn($m) => [
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

                {{-- Lớp phủ chỉ tối ở khoảng 1/3 dưới màn hình, phần trên/giữa để trong hoàn toàn --}}
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 via-40% to-transparent to-70% pointer-events-none">
                </div>

                <div class="relative z-10 flex flex-col justify-end h-full px-5 sm:px-10 md:px-16 pb-10 sm:pb-14 gap-6">

                    {{-- Khối nội dung chính: căn trái, neo ở dưới --}}
                    <div class="max-w-2xl flex flex-col items-start gap-4 text-left">
                        <span
                            class="font-body text-amber-400 text-xs sm:text-sm tracking-[0.3em] uppercase drop-shadow-[0_2px_6px_rgba(0,0,0,0.7)]">
                            Hơn 20 phim đang chiếu mỗi tuần trên toàn hệ thống
                        </span>

                        <h1 class="font-marquee leading-[1.05] text-4xl sm:text-6xl md:text-7xl tracking-tight
                                                   text-white drop-shadow-[0_4px_18px_rgba(0,0,0,0.85)]">
                            Đắm Chìm Trong<br>Từng Thước Phim
                        </h1>

                        <p
                            class="font-body text-white/90 max-w-xl leading-relaxed text-sm sm:text-base font-light drop-shadow-[0_2px_8px_rgba(0,0,0,0.7)]">
                            Tạm gác lại thế giới ồn ào ngoài kia — bước vào bóng tối rạp chiếu,
                            nơi mỗi câu chuyện được kể trọn vẹn trên màn ảnh rộng.
                        </p>

                        <a href="#danh-sach-phim"
                            class="font-body inline-block bg-white hover:bg-gray-100 text-gray-900 text-sm font-semibold px-7 py-3 rounded-full transition-all duration-300 shadow-2xl mt-1">
                            Đặt Vé Ngay
                        </a>
                    </div>

                    {{-- Thanh điều hướng chọn phim: căn trái, cùng cột với nội dung chính --}}
                    <div class="flex flex-wrap gap-x-6 gap-y-2 font-body">
                        <template x-for="(movie, i) in movies" :key="movie.id">
                            <button @click="switchTo(i)"
                                class="text-xs sm:text-sm pb-1 border-b transition-all duration-300 drop-shadow-[0_2px_6px_rgba(0,0,0,0.7)]"
                                :class="active === i
                                                    ? 'text-amber-400 border-amber-400 opacity-100 font-medium'
                                                    : 'text-white/70 border-transparent hover:text-white'"
                                x-text="movie.title"></button>
                        </template>
                    </div>

                    {{-- Dòng thông số: căn trái, cùng cột, mờ nhẹ hơn vì là thông tin phụ --}}
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

        <div id="danh-sach-phim" class="container mx-auto px-4 py-10">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between mb-10">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-sky-400/70 mb-3">Lịch chiếu rạp</p>
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">
                        {{ $status === 'coming_soon' ? 'Phim Sắp Chiếu' : 'Phim Đang Chiếu' }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-slate-300">Duyệt nhanh phim hot, xem lịch chiếu và đặt vé ngay với giao
                        diện tối hiện đại.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('home', ['status' => 'showing']) }}"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $status === 'showing' ? 'bg-primary text-white' : 'bg-slate-900 text-slate-300 hover:bg-slate-800' }}">Phim
                        đang chiếu</a>
                    <a href="{{ route('home', ['status' => 'coming_soon']) }}"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $status === 'coming_soon' ? 'bg-primary text-white' : 'bg-slate-900 text-slate-300 hover:bg-slate-800' }}">Phim
                        sắp chiếu</a>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
                @forelse($movies as $movie)
                    <article
                        class="group overflow-hidden rounded-[2rem] border border-slate-800/70 bg-slate-950/90 shadow-2xl shadow-slate-950/20 transition hover:-translate-y-1 hover:shadow-2xl">
                        <a href="{{ route('movies.show', $movie->id) }}" class="relative overflow-hidden block">
                            <img src="{{ $movie->poster_url ?? 'https://picsum.photos/seed/' . $movie->id . '/760/1080' }}"
                                alt="{{ $movie->title }}"
                                class="h-96 w-full object-cover transition duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-transparent">
                            </div>
                            <div
                                class="absolute left-4 bottom-4 rounded-full bg-slate-900/90 px-3 py-2 text-xs uppercase tracking-[0.2em] text-slate-200">
                                {{ $movie->status === 'coming_soon' ? 'Sắp chiếu' : 'Đang chiếu' }}
                            </div>
                        </a>

                        <div class="space-y-6 p-6">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h2 class="text-2xl font-semibold text-white">{{ $movie->title }}</h2>
                                        <p class="mt-2 text-slate-400">{{ $movie->genre ?? 'Phim điện ảnh' }}</p>
                                    </div>
                                    <span
                                        class="rounded-full border border-slate-700 bg-slate-900/80 px-3 py-1 text-xs uppercase tracking-[0.24em] text-slate-200">
                                        {{ $movie->duration_minutes }} phút
                                    </span>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @if($movie->age_rating)
                                        <span
                                            class="rounded-full border border-slate-700 bg-slate-900/80 px-3 py-1 text-[11px] uppercase tracking-[0.18em] text-slate-200">{{ $movie->age_rating }}</span>
                                    @endif
                                    @foreach($movie->tags->take(2) as $tag)
                                        <span
                                            class="rounded-full border border-slate-700 bg-slate-900/80 px-3 py-1 text-[11px] text-slate-300">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="space-y-4">
                                @if($movie->showtimes->count())
                                    @php
                                        $groupedShowtimes = $movie->showtimes->groupBy(fn($showtime) => $showtime->start_time->locale('vi')->isoFormat('dddd, DD/MM'));
                                    @endphp
                                    @foreach($groupedShowtimes as $dateLabel => $showtimes)
                                        <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-4">
                                            <div class="flex items-center justify-between text-sm font-semibold text-slate-100 mb-3">
                                                <span>{{ $dateLabel }}</span>
                                                <span class="text-slate-400">{{ $showtimes->count() }} suất</span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                @foreach($showtimes as $showtime)
                                                    <a href="{{ url('/showtimes/' . $showtime->id . '/seats') }}"
                                                        class="rounded-2xl border border-slate-800 bg-slate-950/80 px-3 py-2 text-center text-sm text-slate-200 transition hover:border-primary hover:text-white">
                                                        {{ $showtime->start_time->format('H:i') }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-4 text-slate-400">Chưa có lịch
                                        chiếu sẵn sàng.</div>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 mt-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('movies.show', $movie->id) }}"
                                        class="inline-flex items-center gap-2 rounded-full border border-primary px-4 py-2 text-sm font-semibold text-primary transition hover:bg-primary/10">Chi
                                        tiết</a>
                                    @if($movie->trailer_url)
                                        <button @click.prevent="trailerUrl = '{{ $movie->trailer_url }}'; showTrailer = true"
                                            class="inline-flex items-center gap-2 rounded-full bg-slate-800 border border-slate-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 hover:border-slate-600">
                                            <i class="fas fa-play text-xs text-primary"></i> Xem Trailer
                                        </button>
                                    @endif
                                </div>
                                <span
                                    class="text-xs uppercase tracking-[0.2em] text-slate-500">{{ $movie->country ?? 'VN' }}</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-slate-800 bg-slate-950/80 p-8 text-center text-slate-400">
                        Không tìm thấy phim phù hợp với bộ lọc hiện tại.
                    </div>
                @endforelse
            </div>
        </div>
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