@extends('layouts.customer')

@section('title', 'Vé điện tử')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">Thanh toán thành công!</h1>
        <p class="text-gray-400">Dưới đây là vé điện tử của bạn. Vui lòng đưa mã QR này cho nhân viên để check-in.</p>
    </div>

    <!-- Ticket Card -->
    <div class="bg-white rounded-xl shadow-2xl overflow-hidden relative text-gray-900 mx-auto w-full max-w-md">
        
        <!-- Ticket Header -->
        <div class="bg-gray-900 p-6 text-center">
            <h2 class="text-2xl font-bold text-red-500 uppercase tracking-widest">CINEMA TICKET</h2>
        </div>
        
        <!-- Ticket Body -->
        <div class="p-6">
            <h3 class="text-xl font-bold mb-4 border-b pb-2">{{ $booking->showtime->movie->title }}</h3>
            
            <div class="grid grid-cols-2 gap-y-4 gap-x-2 mb-6 text-sm">
                <div>
                    <p class="text-gray-500 font-semibold uppercase text-xs">Rạp</p>
                    <p class="font-bold text-lg">{{ $booking->showtime->room->name }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-semibold uppercase text-xs">Ngày chiếu</p>
                    <p class="font-bold text-lg">{{ \Carbon\Carbon::parse($booking->showtime->start_time)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-semibold uppercase text-xs">Giờ chiếu</p>
                    <p class="font-bold text-lg text-red-600">{{ \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-semibold uppercase text-xs">Mã đặt vé</p>
                    <p class="font-bold text-lg">{{ $booking->booking_code }}</p>
                </div>
            </div>
            
            <div class="mb-4">
                <p class="text-gray-500 font-semibold uppercase text-xs mb-1">Ghế</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($booking->bookingSeats as $bSeat)
                        <span class="inline-block bg-red-100 text-red-800 font-bold px-3 py-1 rounded border border-red-200">
                            {{ $bSeat->showtimeSeat->seat->row }}{{ $bSeat->showtimeSeat->seat->number }}
                        </span>
                    @endforeach
                </div>
            </div>

            @if(!empty($booking->items_data))
                <div class="mb-4">
                    <p class="text-gray-500 font-semibold uppercase text-xs mb-2">Bắp nước & snack</p>
                    <div class="space-y-1 text-sm">
                        @foreach($booking->items_data as $item)
                            <div class="flex justify-between text-gray-700">
                                <span>{{ $item['name'] }} x{{ $item['quantity'] }}</span>
                                <span>{{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 0, ',', '.') }} ₫</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Dashed Divider -->
        <div class="relative flex items-center px-4">
            <div class="h-4 w-4 bg-gray-900 rounded-full absolute -left-2"></div>
            <div class="w-full border-t-2 border-dashed border-gray-300"></div>
            <div class="h-4 w-4 bg-gray-900 rounded-full absolute -right-2"></div>
        </div>
        
        <!-- QR Section -->
        <div class="p-6 bg-gray-50 flex flex-col items-center justify-center">
            <p class="text-sm text-gray-500 mb-4 font-semibold">Quét mã QR để vào rạp</p>
            <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                @if($booking->qr_code_data)
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate($booking->qr_code_data) !!}
                @else
                    <div class="w-48 h-48 bg-gray-200 flex items-center justify-center text-gray-500 text-sm text-center p-4">
                        Mã QR chưa sẵn sàng
                    </div>
                @endif
            </div>
            <p class="text-xs text-gray-400 mt-4 text-center max-w-xs">
                Vé này không được hoàn trả hoặc trao đổi. Vui lòng đến trước 15 phút so với giờ chiếu.
            </p>
        </div>
        
    </div>
    
    <div class="mt-8 text-center">
        <a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Quay lại trang chủ
        </a>
    </div>
</div>
@endsection
