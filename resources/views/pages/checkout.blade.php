@extends('layouts.customer')

@section('title', 'Thanh toán')

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-4xl font-body" x-data="checkoutPage()">
        <div class="bg-dark-card border border-dark-border rounded-lg shadow-lg overflow-hidden flex flex-col md:flex-row liquid-glass">
            <div class="p-6 md:w-2/3 border-b md:border-b-0 md:border-r border-dark-border">
                <h1 class="text-2xl font-marquee text-white mb-6">Thông tin vé</h1>

                <div class="flex gap-4 mb-6">
                    <img src="{{ $booking->showtime->movie->poster_url }}" alt="{{ $booking->showtime->movie->title }}"
                        class="w-32 h-48 object-cover rounded shadow">
                    <div>
                        <h2 class="text-xl font-marquee text-primary mb-2">{{ $booking->showtime->movie->title }}</h2>
                        <p class="text-gray-300 mb-1"><span class="font-semibold text-gray-400">Rạp:</span>
                            {{ $booking->showtime->room->name }}</p>
                        <p class="text-gray-300 mb-1"><span class="font-semibold text-gray-400">Suất chiếu:</span>
                            {{ \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i - d/m/Y') }}</p>
                        <p class="text-gray-300 mb-1">
                            <span class="font-semibold text-gray-400">Ghế:</span>
                            @foreach($booking->bookingSeats as $bSeat)
                                {{ $bSeat->showtimeSeat->seat->row }}{{ $bSeat->showtimeSeat->seat->number }}@if(!$loop->last),
                                @endif
                            @endforeach
                        </p>
                    </div>
                </div>

                @if(!empty($booking->items_data))
                    <div class="border-t border-dark-border pt-4 mt-4">
                        <p class="text-gray-400 font-semibold mb-3">Bắp nước & snack</p>
                        @foreach($booking->items_data as $item)
                            <div class="flex items-center justify-between text-sm text-gray-300 mb-2">
                                <span>{{ $item['name'] }} x{{ $item['quantity'] }}</span>
                                <span>{{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 0, ',', '.') }} ₫</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="border-t border-dark-border pt-4 mt-4 flex justify-between items-center">
                    <span class="text-xl text-gray-300 font-semibold">Tổng tiền:</span>
                    <span
                        class="text-3xl font-bold text-primary">{{ number_format($booking->total_amount, 0, ',', '.') }}
                        ₫</span>
                </div>
            </div>

            <div class="p-6 md:w-1/3 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-marquee text-white mb-4">Thanh toán</h3>

                    <div class="bg-dark p-4 rounded-lg mb-6 border border-dark-border relative overflow-hidden liquid-glass">
                        <div class="absolute inset-0 bg-red-500/10 animate-pulse"></div>
                        <div class="relative z-10 text-center">
                            <p class="text-sm text-gray-400 mb-1">Thời gian giữ ghế còn lại</p>
                            <div class="text-3xl font-mono font-bold text-red-500" x-text="formattedTime">05:00</div>
                        </div>
                    </div>
                </div>

                <button @click="processPayment" x-bind:disabled="processing"
                    class="btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!processing">Xác nhận thanh toán</span>
                    <span x-show="processing" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Đang xử lý...
                    </span>
                </button>

                <div x-show="error" x-text="error" class="mt-4 text-red-500 text-sm font-semibold text-center hidden"
                    :class="{'hidden': !error}"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkoutPage', () => ({
                bookingId: {{ $booking->id }},
                totalAmount: {{ $booking->total_amount }},
                holdExpiresAt: '{{ $booking->bookingSeats->first()->showtimeSeat->hold_expires_at->toISOString() }}',
                processing: false,
                error: null,
                timeLeft: 0,

                init() {
                    // Calculate initial time left
                    const expireTime = new Date(this.holdExpiresAt).getTime();
                    this.updateTimeLeft(expireTime);

                    // Start countdown
                    setInterval(() => {
                        this.updateTimeLeft(expireTime);
                    }, 1000);
                },

                updateTimeLeft(expireTime) {
                    const now = new Date().getTime();
                    const diff = expireTime - now;

                    if (diff <= 0) {
                        this.timeLeft = 0;
                        if (!this.processing) {
                            alert('Thời gian giữ ghế đã hết. Vui lòng chọn lại ghế.');
                            window.location.href = `/showtimes/{{ $booking->showtime_id }}/seats`;
                        }
                    } else {
                        this.timeLeft = Math.floor(diff / 1000);
                    }
                },

                get formattedTime() {
                    const minutes = Math.floor(this.timeLeft / 60);
                    const seconds = this.timeLeft % 60;
                    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                },

                async processPayment() {
                    if (this.timeLeft <= 0) return;

                    this.processing = true;
                    this.error = null;

                    try {
                        // 1. Tạo Idempotency Key duy nhất nhằm chống lặp giao dịch (gửi trùng gói tin)
                        const idempotencyKey = crypto.randomUUID();

                        // 2. Gọi chính xác Endpoint checkout của BookingController qua phương thức POST
                        const response = await fetch(`/api/bookings/${this.bookingId}/checkout`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'Idempotency-Key': idempotencyKey, // Đưa Idempotency-Key lên Header đúng thiết kế CheckIdempotencyKey Middleware
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const data = await response.json();

                        if (response.ok && data.payment_url) {
                            // 3. Điều hướng mượt mà sang liên kết Gateway giả lập do Server cung cấp
                            window.location.href = data.payment_url;
                        } else {
                            this.error = data.message || 'Có lỗi xảy ra khi tạo giao dịch thanh toán.';
                            this.processing = false;
                        }
                    } catch (e) {
                        this.error = 'Lỗi kết nối máy chủ. Vui lòng thử lại sau.';
                        this.processing = false;
                    }
                }
            }));
        });
    </script>
@endsection