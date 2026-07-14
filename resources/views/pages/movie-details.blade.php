@extends('layouts.customer')

@section('content')
<!-- Hero Section -->
<div class="relative w-full overflow-hidden bg-black/90 pt-8 pb-12 md:pt-16 md:pb-24">
    <!-- Blurred Background -->
    <div class="absolute inset-0 opacity-20 blur-3xl scale-110" style="background-image: url('{{ $movie->poster_url }}'); background-size: cover; background-position: center;"></div>
    
    <div class="container relative z-10 mx-auto px-4">
        <div class="flex flex-col md:flex-row gap-8 md:gap-12 items-center md:items-start">
            <div class="w-2/3 sm:w-1/2 md:w-1/4 shrink-0">
                <img src="{{ $movie->poster_url ?? 'https://via.placeholder.com/400x600' }}" alt="{{ $movie->title }}" class="w-full rounded-2xl shadow-2xl border border-white/10">
            </div>
            
            <div class="flex-1 text-center md:text-left text-white space-y-6 mt-4 md:mt-0">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-marquee tracking-tight">{{ $movie->title }}</h1>
                
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                    <span class="bg-primary text-white text-sm px-3 py-1 rounded font-semibold">{{ $movie->age_rating }}</span>
                    <div class="flex items-center gap-1.5 text-white/80 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>{{ $movie->duration_minutes }} phút</span>
                    </div>
                    @foreach($movie->tags as $tag)
                        <span class="border border-white/30 bg-white/5 text-white/80 text-sm px-3 py-1 rounded">{{ $tag->name }}</span>
                    @endforeach
                    @if($movie->trailer_url)
                        <button @click.prevent="trailerUrl = '{{ $movie->trailer_url }}'; showTrailer = true"
                            class="btn-secondary text-sm px-4 py-1.5">
                            <i class="fas fa-play text-xs mr-2"></i> Xem Trailer
                        </button>
                    @endif
                </div>
                
                <div class="font-body text-white/70 text-lg leading-relaxed max-w-3xl">
                    <p>{{ $movie->description }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Movie Information Section -->
<div class="container mx-auto px-4 py-12 font-body">
    <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
        <div class="rounded-2xl border border-dark-border bg-dark-card p-6 shadow-xl liquid-glass">
            <h2 class="text-2xl font-marquee text-white">Nội dung phim</h2>
            <p class="mt-4 whitespace-pre-line text-slate-300 leading-7">
                {{ $movie->content ?: 'Thông tin nội dung phim sẽ được cập nhật sớm.' }}
            </p>
        </div>

        <div class="rounded-2xl border border-dark-border bg-dark-card p-6 shadow-xl liquid-glass">
            <h2 class="text-2xl font-marquee text-white">Thông tin chi tiết</h2>
            <dl class="mt-4 space-y-4 text-sm text-slate-300">
                <div>
                    <dt class="font-semibold text-slate-400">Đạo diễn</dt>
                    <dd class="mt-1 text-white">{{ $movie->director ?: 'Đang cập nhật' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-400">Diễn viên</dt>
                    <dd class="mt-1 text-white">{{ $movie->actors ?: 'Đang cập nhật' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<!-- Showtimes Section -->
<div class="container mx-auto px-4 py-12 font-body">
    <h2 class="text-3xl font-marquee mb-8 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar text-primary"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
        Lịch Chiếu
    </h2>

    <div x-data="{ selectedDate: '{{ $selectedDate }}' }" class="w-full">
        <!-- Date Tabs -->
        <div class="w-full overflow-x-auto pb-4">
            <div class="flex gap-2 p-1 bg-slate-800/50 rounded-xl w-max min-w-full">
                @foreach($dates as $date)
                @php 
                    $dateObj = \Carbon\Carbon::parse($date);
                    $displayDay = $dateObj->isToday() ? 'Hôm nay' : $dateObj->locale('vi')->isoFormat('dddd');
                    $displayDate = $dateObj->format('d/m');
                @endphp
                <a href="{{ route('movies.show', ['movie' => $movie->id, 'date' => $date]) }}" 
                   class="flex flex-col py-3 px-6 rounded-lg min-w-[110px] text-center transition-colors {{ $selectedDate === $date ? 'bg-primary text-white' : 'text-slate-400 hover:text-white hover:bg-slate-700' }}">
                    <span class="text-xs uppercase font-medium opacity-80">{{ $displayDay }}</span>
                    <span class="text-lg font-bold">{{ $displayDate }}</span>
                </a>
                @endforeach
            </div>
        </div>
        
        <!-- Showtimes List -->
        <div class="mt-8">
            @if($showtimes->count() > 0)
                <div class="bg-dark-card border border-dark-border rounded-xl p-6 liquid-glass">
                    <div class="flex flex-wrap gap-4">
                        @foreach($showtimes as $st)
                        <a href="{{ url('/showtimes/'.$st->id.'/seats') }}" class="flex flex-col gap-1 items-center justify-center border border-slate-700 hover:border-primary hover:text-primary bg-slate-800 hover:bg-slate-800/50 transition-colors py-4 px-6 rounded-xl">
                            <span class="font-bold text-xl">{{ \Carbon\Carbon::parse($st->start_time)->format('H:i') }}</span>
                            <span class="text-xs text-slate-400">{{ $st->room->name }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-16 bg-slate-800/30 rounded-xl border border-dashed border-slate-700">
                    <p class="text-xl text-slate-400">Không có suất chiếu nào trong ngày này.</p>
                    <p class="text-sm text-slate-500 mt-2">Vui lòng chọn một ngày khác.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
