@extends('layouts.customer')

@section('content')
<div class="container mx-auto px-4 py-8 space-y-12">
    <!-- Featured Banner -->
    @if($featuredMovies->count() > 0)
    <section>
        <!-- Simple pure CSS/Alpine carousel -->
        <div x-data="{ activeSlide: 0, slides: {{ $featuredMovies->count() }} }" class="relative w-full h-[400px] md:h-[500px] rounded-xl overflow-hidden group">
            @foreach($featuredMovies as $index => $movie)
            <div x-show="activeSlide === {{ $index }}" x-transition.opacity.duration.700ms class="absolute inset-0">
                <img src="{{ $movie->poster_url ?? 'https://via.placeholder.com/1200x500' }}" alt="{{ $movie->title }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-8 w-full md:w-2/3">
                    <div class="flex gap-2 mb-3">
                        <span class="bg-primary text-white text-xs px-2 py-1 rounded font-semibold">{{ $movie->age_rating }}</span>
                        @foreach($movie->tags->take(3) as $tag)
                            <span class="bg-slate-700 text-slate-300 text-xs px-2 py-1 rounded">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                    <h2 class="text-3xl md:text-5xl font-bold text-white mb-4 shadow-sm">{{ $movie->title }}</h2>
                    <a href="{{ route('movies.show', $movie->id) }}" class="inline-block bg-primary hover:bg-red-700 text-white font-medium py-3 px-8 rounded-full transition-colors shadow-lg shadow-red-500/30">
                        Mua vé ngay
                    </a>
                </div>
            </div>
            @endforeach
            
            <!-- Controls -->
            <button @click="activeSlide = activeSlide === 0 ? slides - 1 : activeSlide - 1" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/50 text-white rounded-full flex items-center justify-center hover:bg-primary transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <button @click="activeSlide = activeSlide === slides - 1 ? 0 : activeSlide + 1" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/50 text-white rounded-full flex items-center justify-center hover:bg-primary transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    </section>
    @endif

    <!-- Movies List -->
    <section>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <h2 class="text-3xl font-bold tracking-tight">Phim Đang Chiếu</h2>
            
            <form action="{{ route('home') }}" method="GET" class="overflow-x-auto w-full md:w-auto pb-2 flex gap-2" id="filter-form">
                @php $selectedTags = explode(',', request('tags', '')); @endphp
                @foreach($tags as $tag)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="tag[]" value="{{ $tag->slug }}" class="hidden peer tag-checkbox" @if(in_array($tag->slug, $selectedTags)) checked @endif>
                        <div class="px-4 py-2 rounded-full border border-slate-700 bg-slate-800 text-sm peer-checked:bg-primary peer-checked:border-primary peer-checked:text-white transition-colors whitespace-nowrap">
                            {{ $tag->name }}
                        </div>
                    </label>
                @endforeach
                <input type="hidden" name="tags" id="tags-input" value="{{ request('tags') }}">
            </form>
        </div>

        @if($movies->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($movies as $movie)
            <div class="bg-slate-900 rounded-xl overflow-hidden group border border-slate-800 hover:border-slate-700 transition">
                <div class="relative overflow-hidden aspect-[2/3]">
                    <img src="{{ $movie->poster_url ?? 'https://via.placeholder.com/300x450' }}" alt="{{ $movie->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute top-2 left-2">
                        <span class="bg-primary text-white text-xs px-2 py-1 rounded shadow">{{ $movie->age_rating }}</span>
                    </div>
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                        <a href="{{ route('movies.show', $movie->id) }}" class="bg-primary text-white font-medium py-2 px-6 rounded-full shadow-lg hover:bg-red-700 transition-colors">
                            Đặt vé
                        </a>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-bold line-clamp-1 group-hover:text-primary transition-colors">{{ $movie->title }}</h3>
                    <div class="flex gap-1 flex-wrap mt-2">
                        @foreach($movie->tags->take(2) as $tag)
                            <span class="text-xs text-slate-400 border border-slate-700 px-2 py-0.5 rounded-full">{{ $tag->name }}</span>
                        @endforeach
                        @if($movie->tags->count() > 2)
                            <span class="text-xs text-slate-500 ml-1">+{{ $movie->tags->count() - 2 }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-24 text-slate-400 bg-slate-800/50 rounded-xl">
            <p class="text-xl font-medium mb-2">Không tìm thấy phim nào</p>
            <p>Vui lòng thử bỏ chọn một số thể loại lọc.</p>
            @if(request('tags'))
                <a href="{{ route('home') }}" class="inline-block mt-4 text-primary hover:underline">Xóa bộ lọc</a>
            @endif
        </div>
        @endif
    </section>
</div>

<script>
    // Handle filter form submission
    document.querySelectorAll('.tag-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
            const checked = Array.from(document.querySelectorAll('.tag-checkbox:checked')).map(el => el.value);
            document.getElementById('tags-input').value = checked.join(',');
            document.getElementById('filter-form').submit();
        });
    });
</script>
@endsection
