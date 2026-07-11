@extends('layouts.staff')

@section('content')
<div class="no-print">
    <div class="mb-8 bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Lựa chọn suất chiếu</h2>
        <form method="GET" action="{{ route('staff.counter') }}" class="flex gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700">Ngày chiếu</label>
                <input type="date" name="date" value="{{ $date }}" class="mt-1 border-gray-300 rounded-md shadow-sm p-3 text-lg" onchange="this.form.submit()">
            </div>
            
            <div class="flex-grow">
                <label class="block text-sm font-medium text-gray-700">Suất chiếu</label>
                <select name="showtime_id" class="mt-1 border-gray-300 rounded-md shadow-sm w-full p-3 text-lg" onchange="this.form.submit()">
                    <option value="">-- Chọn suất chiếu --</option>
                    @foreach($showtimes as $st)
                        <option value="{{ $st->id }}" {{ (isset($selectedShowtime) && $selectedShowtime->id == $st->id) ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($st->start_time)->format('H:i') }} - {{ $st->movie->title }} ({{ $st->room->name }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if(isset($selectedShowtime) && isset($seats))
    <div class="flex flex-col lg:flex-row gap-6" x-data="counterBooking()">
        <!-- Sơ đồ ghế -->
        <div class="bg-white shadow rounded-lg p-6 lg:w-2/3">
            <h3 class="text-xl font-bold mb-6 text-center border-b pb-2">Sơ đồ ghế: {{ $selectedShowtime->room->name }}</h3>
            
            <div class="mb-10 text-center">
                <div class="w-2/3 mx-auto h-8 bg-gray-300 text-gray-600 font-bold leading-8 rounded-t-xl">MÀN HÌNH</div>
            </div>
            
            @php
                $groupedSeats = collect($seats)->groupBy('row');
            @endphp
            
            <div class="flex flex-col items-center gap-2">
                @foreach($groupedSeats as $row => $rowSeats)
                    <div class="flex gap-2 items-center">
                        <div class="w-8 text-center font-bold text-gray-500">{{ $row }}</div>
                        <div class="flex gap-2">
                            @foreach($rowSeats as $seat)
                                <button 
                                    @click="toggleSeat({{ json_encode($seat) }})"
                                    class="w-10 h-10 rounded border-b-4 text-xs font-bold transition flex justify-center items-center"
                                    :class="{
                                        'bg-gray-200 border-gray-400 text-gray-800 hover:bg-gray-300': '{{ $seat['status'] }}' === 'available' && !isSelected({{ $seat['seat_id'] }}),
                                        'bg-red-500 border-red-700 text-white cursor-not-allowed': '{{ $seat['status'] }}' !== 'available',
                                        'bg-green-500 border-green-700 text-white': isSelected({{ $seat['seat_id'] }})
                                    }"
                                    {{ $seat['status'] !== 'available' ? 'disabled' : '' }}
                                >
                                    {{ $seat['number'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-8 flex justify-center gap-6 border-t pt-4">
                <div class="flex items-center gap-2"><div class="w-6 h-6 bg-gray-200 border-b-4 border-gray-400 rounded"></div><span class="font-medium">Trống</span></div>
                <div class="flex items-center gap-2"><div class="w-6 h-6 bg-red-500 border-b-4 border-red-700 rounded"></div><span class="font-medium">Đã đặt</span></div>
                <div class="flex items-center gap-2"><div class="w-6 h-6 bg-green-500 border-b-4 border-green-700 rounded"></div><span class="font-medium">Đang chọn</span></div>
            </div>
        </div>

        <!-- Checkout -->
        <div class="bg-white shadow rounded-lg p-6 lg:w-1/3 flex flex-col justify-between">
            <div>
                <h3 class="text-xl font-bold mb-4 border-b pb-2">Thông tin vé</h3>
                <div class="mb-2"><strong>Phim:</strong> {{ $selectedShowtime->movie->title }}</div>
                <div class="mb-2"><strong>Thời gian:</strong> {{ \Carbon\Carbon::parse($selectedShowtime->start_time)->format('H:i d/m/Y') }}</div>
                
                <div class="mt-4 mb-2 font-bold">Ghế đã chọn:</div>
                <div class="flex flex-wrap gap-2 mb-4 min-h-[3rem]">
                    <template x-for="seat in selectedSeats" :key="seat.seat_id">
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded font-bold border border-green-300" x-text="seat.row + seat.number"></span>
                    </template>
                    <div x-show="selectedSeats.length === 0" class="text-gray-400 italic">Chưa chọn ghế nào</div>
                </div>
                
                <div class="flex justify-between items-end border-t pt-4 mt-4">
                    <span class="text-gray-600 font-bold text-lg">Tổng tiền:</span>
                    <span class="text-3xl font-bold text-green-600" x-text="formatMoney(totalAmount) + ' ₫'">0 ₫</span>
                </div>
            </div>
            
            <div class="mt-6">
                <div x-show="error" class="mb-4 bg-red-100 text-red-700 p-3 rounded" x-text="error"></div>
                <button 
                    @click="processTicket" 
                    :disabled="selectedSeats.length === 0 || processing"
                    class="w-full py-4 rounded-lg font-bold text-xl text-white transition disabled:bg-gray-400 disabled:cursor-not-allowed"
                    :class="processing ? 'bg-gray-400' : 'bg-indigo-600 hover:bg-indigo-700'"
                >
                    <span x-show="!processing">Thu tiền & Xuất vé</span>
                    <span x-show="processing">Đang xử lý...</span>
                </button>
            </div>
        </div>
        
        <!-- Modal In Vé -->
        <div x-show="ticketData" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-75 flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm p-6 text-center">
                <h2 class="text-2xl font-bold text-green-600 mb-2">Thanh toán thành công!</h2>
                <p class="mb-6 text-gray-600">Đã thu tiền và xuất vé.</p>
                
                <button @click="printTicket" class="w-full py-3 bg-blue-600 text-white font-bold rounded-lg mb-3 hover:bg-blue-700 text-lg">
                    In Vé (Print)
                </button>
                <button @click="window.location.reload()" class="w-full py-3 bg-gray-200 text-gray-800 font-bold rounded-lg hover:bg-gray-300">
                    Bán tiếp vé khác
                </button>
            </div>
        </div>

        <!-- Dữ liệu in ẩn -->
        <div id="print-area" class="print-only text-black" x-html="printHtml">
            <!-- Sẽ chèn HTML vào đây qua JS -->
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('counterBooking', () => ({
                showtimeId: {{ $selectedShowtime->id }},
                selectedSeats: [],
                processing: false,
                error: null,
                ticketData: null,
                printHtml: '',

                get totalAmount() {
                    return this.selectedSeats.reduce((sum, seat) => sum + seat.price, 0);
                },

                isSelected(seatId) {
                    return this.selectedSeats.some(s => s.seat_id === seatId);
                },

                toggleSeat(seat) {
                    if (seat.status !== 'available') return;
                    
                    const index = this.selectedSeats.findIndex(s => s.seat_id === seat.seat_id);
                    if (index > -1) {
                        this.selectedSeats.splice(index, 1);
                    } else {
                        if (this.selectedSeats.length >= 8) {
                            alert('Chỉ được chọn tối đa 8 ghế một lần.');
                            return;
                        }
                        this.selectedSeats.push(seat);
                    }
                },

                formatMoney(amount) {
                    return new Intl.NumberFormat('vi-VN').format(amount);
                },

                async processTicket() {
                    if (this.selectedSeats.length === 0) return;
                    this.processing = true;
                    this.error = null;

                    const seatIds = this.selectedSeats.map(s => s.seat_id); // Chú ý gửi seat_id (bảng seats) theo API
                    const idempotencyKey = crypto.randomUUID();

                    try {
                        const response = await fetch('/api/staff/bookings/counter', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'Idempotency-Key': idempotencyKey,
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                showtime_id: this.showtimeId,
                                seat_ids: seatIds,
                                payment_method: 'cash'
                            })
                        });

                        const data = await response.json();

                        if (response.ok || (data.message && data.message === 'Ticket issued successfully')) {
                            this.ticketData = data.booking;
                            this.preparePrintHtml();
                        } else {
                            this.error = data.message || 'Lỗi thanh toán.';
                            this.processing = false;
                        }
                    } catch (e) {
                        this.error = 'Lỗi kết nối mạng.';
                        this.processing = false;
                    }
                },

                preparePrintHtml() {
                    const seatNames = this.selectedSeats.map(s => s.row + s.number).join(', ');
                    const title = `{{ $selectedShowtime->movie->title }}`;
                    const room = `{{ $selectedShowtime->room->name }}`;
                    const time = `{{ \Carbon\Carbon::parse($selectedShowtime->start_time)->format('H:i d/m/Y') }}`;
                    
                    this.printHtml = `
                        <div style="width: 300px; padding: 20px; font-family: monospace; border: 1px dashed black;">
                            <h2 style="text-align:center; font-size: 24px; margin:0 0 10px;">CINEMA TICKET</h2>
                            <p style="text-align:center; margin:0 0 20px;">Mã vé: ${this.ticketData.booking_code}</p>
                            <hr style="border-top:1px dashed #ccc;">
                            <p><strong>Phim:</strong> ${title}</p>
                            <p><strong>Rạp:</strong> ${room}</p>
                            <p><strong>Giờ chiếu:</strong> ${time}</p>
                            <p><strong>Ghế:</strong> ${seatNames}</p>
                            <hr style="border-top:1px dashed #ccc;">
                            <p style="font-size: 18px; font-weight:bold; text-align:right;">Tổng: ${this.formatMoney(this.totalAmount)} VND</p>
                            <p style="text-align:center; margin-top:20px; font-size:12px;">Cảm ơn quý khách!</p>
                        </div>
                    `;
                },

                printTicket() {
                    window.print();
                }
            }));
        });
    </script>
    @endif
</div>
@endsection
