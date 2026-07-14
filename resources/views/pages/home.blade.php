@extends('layouts.customer')

@section('content')

    <div class="min-h-screen bg-dark text-white font-body">

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
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 via-40% to-transparent to-70% pointer-events-none"></div>

                <div class="relative z-10 flex flex-col justify-end h-full px-5 sm:px-10 md:px-16 pb-10 sm:pb-14 gap-6">

                    {{-- Khối nội dung chính: căn trái, neo ở dưới --}}
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

                    {{-- Thanh điều hướng chọn phim: căn trái, cùng cột với nội dung chính --}}
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

                    {{-- Dòng thông số: căn trái, cùng cột, mờ nhẹ hơn vì là thông tin phụ --}}
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

        <div id="danh-sach-phim" class="container mx-auto px-4 py-16">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between mb-12">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-primary/80 mb-3 font-body">Lịch chiếu rạp</p>
                    <h1 class="text-4xl md:text-5xl font-marquee tracking-tight text-white">
                        {{ $status === 'coming_soon' ? 'Phim Sắp Chiếu' : 'Phim Đang Chiếu' }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-slate-400 font-body">
                        Duyệt nhanh phim hot, xem lịch chiếu và đặt vé ngay với giao diện tối hiện đại.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 font-body">
                    <a href="{{ route('home', ['status' => 'showing']) }}"
                        class="rounded-full px-5 py-2.5 text-sm font-semibold transition-all duration-300 {{ $status === 'showing' ? 'bg-primary text-black shadow-glow-primary' : 'bg-dark-card border-dark-border border text-slate-300 hover:bg-slate-800' }}">
                        Phim đang chiếu
                    </a>
                    <a href="{{ route('home', ['status' => 'coming_soon']) }}"
                        class="rounded-full px-5 py-2.5 text-sm font-semibold transition-all duration-300 {{ $status === 'coming_soon' ? 'bg-primary text-black shadow-glow-primary' : 'bg-dark-card border-dark-border border text-slate-300 hover:bg-slate-800' }}">
                        Phim sắp chiếu
                    </a>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-2 xl:grid-cols-3">
                @forelse($movies as $movie)
                    <article class="group overflow-hidden rounded-[2rem] border border-dark-border bg-dark-card shadow-2xl transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_12px_40px_rgba(0,0,0,0.5)] font-body">
                        
                        {{-- Poster phim bọc hiệu ứng thiết kế mới --}}
                        <a href="{{ route('movies.show', $movie->id) }}" class="block poster-card rounded-b-none">
                            <img src="{{ $movie->poster_url ?? 'https://picsum.photos/seed/' . $movie->id . '/760/1080' }}"
                                alt="{{ $movie->title }}"
                                class="h-96 w-full object-cover transition duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-dark via-transparent to-transparent"></div>
                            <div class="absolute left-4 bottom-4 rounded-full bg-black/80 backdrop-blur-sm border border-white/10 px-3 py-1.5 text-xs uppercase tracking-[0.2em] text-slate-200">
                                {{ $movie->status === 'coming_soon' ? 'Sắp chiếu' : 'Đang chiếu' }}
                            </div>
                        </a>

                        <div class="space-y-6 p-6">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h2 class="text-2xl font-marquee text-white tracking-wide leading-tight">{{ $movie->title }}</h2>
                                        <p class="mt-1 text-slate-400 text-sm font-light">{{ $movie->genre ?? 'Phim điện ảnh' }}</p>
                                    </div>
                                    <span class="rounded-full border border-dark-border bg-black/60 px-3 py-1 text-xs uppercase tracking-[0.15em] text-slate-300 font-medium">
                                        {{ $movie->duration_minutes }} phút
                                    </span>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @if($movie->age_rating)
                                        <span class="rounded-full border border-red-500/20 bg-red-500/10 px-3 py-1 text-[11px] uppercase tracking-[0.18em] text-red-400 font-semibold">
                                            {{ $movie->age_rating }}
                                        </span>
                                    @endif
                                    @foreach($movie->tags->take(2) as $tag)
                                        <span class="rounded-full border border-dark-border bg-black/40 px-3 py-1 text-[11px] text-slate-400 font-light">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Khối suất chiếu dạng Kính mờ Liquid Glass --}}
                            <div class="space-y-4">
                                @if($movie->showtimes->count())
                                    @php
                                        $groupedShowtimes = $movie->showtimes->groupBy(fn($showtime) => $showtime->start_time->locale('vi')->isoFormat('dddd, DD/MM'));
                                    @endphp
                                    @foreach($groupedShowtimes as $dateLabel => $showtimes)
                                        <div class="liquid-glass rounded-2xl p-4 border border-white/5">
                                            <div class="flex items-center justify-between text-sm font-semibold text-slate-200 mb-3">
                                                <span class="capitalize">{{ $dateLabel }}</span>
                                                <span class="text-primary text-xs tracking-wider">{{ $showtimes->count() }} suất chiếu</span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                @foreach($showtimes as $showtime)
                                                    <a href="{{ url('/showtimes/' . $showtime->id . '/seats') }}"
                                                        class="rounded-xl border border-white/5 bg-black/40 px-3 py-2 text-center text-sm text-slate-300 transition-all duration-300 hover:border-primary hover:text-primary hover:scale-[1.03]">
                                                        {{ $showtime->start_time->format('H:i') }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="rounded-2xl border border-dark-border bg-black/25 p-4 text-slate-500 text-sm font-light">
                                        Chưa có lịch chiếu sẵn sàng.
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 mt-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('movies.show', $movie->id) }}" class="btn-secondary text-xs px-5 py-2.5">
                                        Chi tiết
                                    </a>
                                    @if($movie->trailer_url)
                                        <button @click.prevent="trailerUrl = '{{ $movie->trailer_url }}'; showTrailer = true"
                                            class="inline-flex items-center gap-2 rounded-full bg-dark-card border border-dark-border px-5 py-2.5 text-xs font-semibold text-white transition-all duration-300 hover:bg-slate-800 hover:border-white/20">
                                            <i class="fas fa-play text-[10px] text-primary"></i> Xem Trailer
                                        </button>
                                    @endif
                                </div>
                                <span class="text-xs uppercase tracking-[0.2em] text-slate-500 font-medium">
                                    {{ $movie->country ?? 'VN' }}
                                </span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dark-border bg-dark-card/50 p-12 text-center text-slate-400 col-span-full">
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