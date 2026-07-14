@extends('layouts.customer')

@section('title', 'Lịch sử đặt vé')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl font-body">
    <h1 class="text-2xl font-marquee text-white mb-6 border-b border-dark-border pb-4">Lịch sử đặt vé của tôi</h1>

    @if($bookings->isEmpty())
        <div class="bg-dark-card rounded-lg p-8 text-center border border-dark-border liquid-glass">
            <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
            <h2 class="text-xl font-semibold text-gray-300 mb-2">Bạn chưa có vé nào</h2>
            <p class="text-gray-500 mb-6">Hãy đặt vé để xem những bộ phim hấp dẫn tại rạp của chúng tôi.</p>
            <a href="{{ route('home') }}" class="btn-primary mt-2">
                Xem lịch chiếu ngay
            </a>
        </div>
    @else
        <div class="grid gap-6">
            @foreach($bookings as $booking)
                <div class="bg-dark-card rounded-lg shadow-lg overflow-hidden border border-dark-border hover:border-primary transition flex flex-col md:flex-row liquid-glass">
                    <!-- Movie Poster (mobile hidden or small) -->
                    <div class="w-full md:w-32 h-48 md:h-auto bg-gray-900 shrink-0">
                        @if($booking->showtime && $booking->showtime->movie)
                        <img src="{{ $booking->showtime->movie->poster_url }}" alt="Poster" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-gray-600">No Image</div>
                        @endif
                    </div>
                    
                    <!-- Details -->
                    <div class="p-5 flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-xl font-marquee text-white">
                                    {{ $booking->showtime->movie->title ?? 'Unknown Movie' }}
                                </h3>
                                
                                @if($booking->status == 'paid')
                                    <span class="px-2 py-1 bg-green-900/50 text-green-400 text-xs font-bold rounded uppercase">Đã thanh toán</span>
                                @elseif($booking->status == 'pending')
                                    <span class="px-2 py-1 bg-yellow-900/50 text-yellow-400 text-xs font-bold rounded uppercase">Chờ thanh toán</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-700 text-gray-300 text-xs font-bold rounded uppercase">{{ $booking->status }}</span>
                                @endif
                            </div>
                            
                            <div class="text-sm text-gray-400 grid grid-cols-2 gap-y-2 mt-4">
                                <div><span class="text-gray-500">Mã vé:</span> <span class="font-semibold text-gray-300">{{ $booking->booking_code }}</span></div>
                                <div><span class="text-gray-500">Rạp:</span> <span class="font-semibold text-gray-300">{{ $booking->showtime->room->name ?? '-' }}</span></div>
                                <div><span class="text-gray-500">Thời gian:</span> <span class="font-semibold text-primary">{{ \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i - d/m/Y') }}</span></div>
                                <div><span class="text-gray-500">Tổng tiền:</span> <span class="font-semibold text-primary">{{ number_format($booking->total_amount, 0, ',', '.') }} ₫</span></div>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-dark-border flex justify-end">
                            @if($booking->status == 'paid')
                                <a href="{{ route('tickets.show', $booking->id) }}" class="text-sm font-semibold text-red-500 hover:text-red-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                                    Xem vé điện tử
                                </a>
                            @elseif($booking->status == 'pending')
                                <a href="{{ route('checkout', $booking->id) }}" class="text-sm font-semibold text-blue-500 hover:text-blue-400 flex items-center gap-1">
                                    Tiếp tục thanh toán
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
