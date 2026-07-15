@extends('layouts.staff')

@section('content')
<div>
    <div class="mb-8 bg-white shadow rounded-lg p-6 no-print">
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
    <div x-data="counterBooking()">
        <div class="flex flex-col lg:flex-row gap-6 no-print">
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
                                @php
                                    $seatType = $seat['seat_type'] ?? $seat['type'] ?? 'standard';
                                    $seatClass = $seat['status'] !== 'available'
                                        ? 'bg-red-500 border-red-700 text-white cursor-not-allowed'
                                        : ($seatType === 'vip'
                                            ? 'bg-amber-500 border-amber-600 text-white hover:bg-amber-600'
                                            : ($seatType === 'couple'
                                                ? 'bg-fuchsia-500 border-fuchsia-600 text-white hover:bg-fuchsia-600'
                                                : 'bg-gray-200 border-gray-400 text-gray-800 hover:bg-gray-300'));
                                @endphp
                                <button
                                    @click="toggleSeat({{ json_encode($seat) }})"
                                    class="w-10 h-10 rounded border-b-4 text-xs font-bold transition flex justify-center items-center {{ $seatClass }}"
                                    :class="isSelected({{ $seat['seat_id'] }}) ? 'bg-green-500 border-green-700 text-white' : ''"
                                    {{ $seat['status'] !== 'available' ? 'disabled' : '' }}
                                >
                                    {{ $seat['number'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-8 flex flex-wrap justify-center gap-4 border-t pt-4">
                <div class="flex items-center gap-2"><div class="w-6 h-6 bg-gray-200 border-b-4 border-gray-400 rounded"></div><span class="font-medium">Standard</span></div>
                <div class="flex items-center gap-2"><div class="w-6 h-6 bg-amber-500 border-b-4 border-amber-600 rounded"></div><span class="font-medium">VIP</span></div>
                <div class="flex items-center gap-2"><div class="w-6 h-6 bg-fuchsia-500 border-b-4 border-fuchsia-600 rounded"></div><span class="font-medium">Couple</span></div>
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

                <div class="mt-4 mb-2 border-t pt-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-bold">Bắp nước & snack:</span>
                        <span class="text-sm font-semibold text-indigo-600" x-text="formatMoney(foodSubtotal) + ' ₫'">0 ₫</span>
                    </div>
                    <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2">
                        <template x-for="item in snackItems" :key="item.id">
                            <div class="flex items-center justify-between p-3 border rounded-lg bg-gray-50">
                                <div>
                                    <div class="font-semibold text-gray-800">
                                        <span x-text="item.icon"></span> <span x-text="item.name"></span>
                                    </div>
                                    <div class="text-sm text-gray-500" x-text="formatMoney(item.price) + ' ₫'"></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="removeItem(item)" type="button" class="w-8 h-8 rounded-full bg-white border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100">−</button>
                                    <span class="w-6 text-center font-bold" x-text="getItemQuantity(item.id)"></span>
                                    <button @click="addItem(item)" type="button" class="w-8 h-8 rounded-full bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-600 hover:bg-indigo-100">+</button>
                                </div>
                            </div>
                        </template>
                    </div>
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
        </div>
        
        <!-- Modal In Vé -->
        <div x-show="ticketData" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-75 flex items-center justify-center p-4 no-print" style="display: none;">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm p-6 text-center">
                <h2 class="text-2xl font-bold text-green-600 mb-2">Thanh toán thành công!</h2>
                <p class="mb-6 text-gray-600">Đã thu tiền và xuất vé.</p>

                <div class="mb-4 rounded-lg border border-dashed border-gray-300 p-4 text-left bg-gray-50">
                    <div class="text-center text-lg font-bold uppercase tracking-wide text-gray-800">CINEMA TICKET</div>
                    <div class="mt-3 space-y-1 text-sm text-gray-700">
                        <div><strong>Mã vé:</strong> <span x-text="ticketData?.booking_code"></span></div>
                        <div><strong>Phim:</strong> <span x-text="selectedShowtimeTitle"></span></div>
                        <div><strong>Rạp:</strong> <span x-text="selectedRoomName"></span></div>
                        <div><strong>Giờ chiếu:</strong> <span x-text="selectedShowtimeTime"></span></div>
                        <div><strong>Ghế:</strong> <span x-text="selectedSeatNames"></span></div>
                        <template x-if="selectedItems.length > 0">
                            <div>
                                <strong>Bắp nước:</strong>
                                <ul class="list-disc pl-5 mt-1">
                                    <template x-for="item in selectedItems" :key="item.id">
                                        <li><span x-text="item.name"></span> x<span x-text="item.quantity"></span></li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                    </div>
                </div>

                <button @click="printTicket" class="w-full py-3 bg-blue-600 text-white font-bold rounded-lg mb-3 hover:bg-blue-700 text-lg">
                    Xuất vé
                </button>
                <button @click="cancelBooking" :disabled="processing" class="w-full py-3 bg-red-600 text-white font-bold rounded-lg mb-3 hover:bg-red-700 disabled:bg-red-300 text-lg">
                    <span x-show="!processing">Hủy đặt ghế</span>
                    <span x-show="processing">Đang xử lý...</span>
                </button>
                <button @click="closeTicketModal" class="w-full py-3 bg-gray-200 text-gray-800 font-bold rounded-lg hover:bg-gray-300">
                    Đóng
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
                selectedItems: [],
                snackItems: [
                    { id: 'popcorn_small', name: 'Bắp rang nhỏ', price: 35000, icon: '🍿', description: 'Phù hợp cho một người' },
                    { id: 'popcorn_large', name: 'Bắp rang lớn', price: 55000, icon: '🍿', description: 'Cặp đôi hoặc nhóm xem phim' },
                    { id: 'soda_small', name: 'Nước ngọt nhỏ', price: 25000, icon: '🥤', description: 'Ly nhỏ, giải khát nhanh' },
                    { id: 'soda_large', name: 'Nước ngọt lớn', price: 35000, icon: '🥤', description: 'Ly lớn, uống thoải mái' },
                    { id: 'snack_combo', name: 'Combo snack', price: 65000, icon: '🍿', description: 'Bắp + nước + snack' },
                ],
                processing: false,
                error: null,
                ticketData: null,
                printHtml: '',

                isSelected(seatId) {
                    return this.selectedSeats.some(s => s.seat_id === seatId);
                },

                toggleSeat(seat) {
                    if (seat.status !== 'available') return;
                    
                    const index = this.selectedSeats.findIndex(s => s.seat_id === seat.seat_id);
                    if (index > -1) {
                        this.selectedSeats = this.selectedSeats.filter(s => s.seat_id !== seat.seat_id);
                    } else {
                        if (this.selectedSeats.length >= 8) {
                            alert('Chỉ được chọn tối đa 8 ghế một lần.');
                            return;
                        }
                        this.selectedSeats = [...this.selectedSeats, seat];
                    }
                },

                addItem(item) {
                    const existing = this.selectedItems.find(entry => entry.id === item.id);
                    if (existing) {
                        existing.quantity += 1;
                    } else {
                        this.selectedItems.push({ ...item, quantity: 1 });
                    }
                },

                removeItem(item) {
                    const existing = this.selectedItems.find(entry => entry.id === item.id);
                    if (!existing) return;

                    if (existing.quantity > 1) {
                        existing.quantity -= 1;
                    } else {
                        this.selectedItems = this.selectedItems.filter(entry => entry.id !== item.id);
                    }
                },

                getItemQuantity(itemId) {
                    return this.selectedItems.find(entry => entry.id === itemId)?.quantity || 0;
                },

                get foodSubtotal() {
                    return this.selectedItems.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.quantity || 0), 0);
                },

                get totalAmount() {
                    return this.selectedSeats.reduce((sum, seat) => sum + Number(seat.price || 0), 0) + this.foodSubtotal;
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
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'Idempotency-Key': idempotencyKey,
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                showtime_id: this.showtimeId,
                                seat_ids: seatIds,
                                payment_method: 'cash',
                                items: this.selectedItems.map(({ id, quantity }) => ({ id, quantity }))
                            })
                        });

                        const data = await response.json();

                        if (response.ok || (data.message && data.message === 'Ticket issued successfully')) {
                            this.processing = false;
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
                    
                    let itemsHtml = '';
                    if (this.selectedItems.length > 0) {
                        itemsHtml += '<hr style="border-top:1px dashed #ccc;"><p><strong>Đồ ăn/nước:</strong></p>';
                        this.selectedItems.forEach(item => {
                            itemsHtml += `<p style="margin-left: 10px;">- ${item.name} x${item.quantity}</p>`;
                        });
                    }
                    
                    this.printHtml = `
                        <div style="width: 300px; padding: 20px; font-family: monospace; border: 1px dashed black;">
                            <h2 style="text-align:center; font-size: 24px; margin:0 0 10px;">CINEMA TICKET</h2>
                            <p style="text-align:center; margin:0 0 20px;">Mã vé: ${this.ticketData.booking_code}</p>
                            <hr style="border-top:1px dashed #ccc;">
                            <p><strong>Phim:</strong> ${title}</p>
                            <p><strong>Rạp:</strong> ${room}</p>
                            <p><strong>Giờ chiếu:</strong> ${time}</p>
                            <p><strong>Ghế:</strong> ${seatNames}</p>
                            ${itemsHtml}
                            <hr style="border-top:1px dashed #ccc;">
                            <p style="font-size: 18px; font-weight:bold; text-align:right;">Tổng: ${this.formatMoney(this.totalAmount)} VND</p>
                            <p style="text-align:center; margin-top:20px; font-size:12px;">Cảm ơn quý khách!</p>
                        </div>
                    `;
                },

                get selectedShowtimeTitle() {
                    return '{{ $selectedShowtime->movie->title }}';
                },

                get selectedRoomName() {
                    return '{{ $selectedShowtime->room->name }}';
                },

                get selectedShowtimeTime() {
                    return '{{ \Carbon\Carbon::parse($selectedShowtime->start_time)->format('H:i d/m/Y') }}';
                },

                get selectedSeatNames() {
                    return this.selectedSeats.map(seat => seat.row + seat.number).join(', ');
                },

                printTicket() {
                    this.preparePrintHtml();
                    setTimeout(() => window.print(), 150);
                },

                async cancelBooking() {
                    if (!this.ticketData?.id) return;

                    this.processing = true;
                    this.error = null;

                    try {
                        const response = await fetch(`/api/staff/bookings/${this.ticketData.id}/cancel`, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.ticketData = null;
                            this.printHtml = '';
                            this.selectedSeats = [];
                            window.location.reload();
                            return;
                        }

                        this.error = data.message || 'Không thể hủy đặt ghế.';
                    } catch (e) {
                        this.error = 'Lỗi kết nối mạng.';
                    } finally {
                        this.processing = false;
                    }
                },

                closeTicketModal() {
                    this.ticketData = null;
                    this.printHtml = '';
                    this.selectedSeats = [];
                    window.location.reload();
                }
            }));
        });
    </script>
    </div>
    @endif
</div>
@endsection
