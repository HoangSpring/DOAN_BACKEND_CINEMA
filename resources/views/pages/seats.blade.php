@extends('layouts.customer')

@section('content')
    <div class="container mx-auto px-4 py-8 font-body" x-data="seatSelector()">
        <div
            class="bg-dark-card border border-dark-border shadow-2xl shadow-slate-900/50 rounded-2xl p-6 mb-12 flex flex-col lg:flex-row gap-8 relative overflow-hidden liquid-glass">
            <div
                class="absolute top-0 right-0 w-64 h-64 bg-primary/10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none">
            </div>

            <!-- Movie Poster -->
            <div class="relative z-10 w-32 sm:w-48 shrink-0 mx-auto lg:mx-0 poster-card border-none">
                <img src="{{ $showtime->movie->poster_url ?? 'https://via.placeholder.com/300x450' }}"
                    alt="{{ $showtime->movie->title }}" class="w-full rounded-xl shadow-lg object-cover aspect-[2/3]">
            </div>

            <!-- Movie Info -->
            <div class="relative z-10 flex-1 flex flex-col justify-center text-center lg:text-left">
                <h1
                    class="text-2xl sm:text-3xl md:text-4xl font-marquee text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400 mb-4 tracking-wide">
                    {{ $showtime->movie->title }}
                </h1>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 text-sm mb-4">
                    <span
                        class="bg-slate-800 text-slate-300 px-3 py-1 rounded-full border border-slate-700 font-semibold">{{ $showtime->movie->age_rating }}</span>
                    <span
                        class="bg-slate-800 text-slate-300 px-3 py-1 rounded-full border border-slate-700">{{ $showtime->movie->duration_minutes }}
                        phút</span>
                    <span class="bg-slate-800 text-slate-300 px-3 py-1 rounded-full border border-slate-700">Phòng: <strong
                            class="text-white">{{ $showtime->room->name }}</strong></span>
                    <span class="bg-primary/20 text-primary px-3 py-1 rounded-full font-semibold border border-primary/30">
                        {{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i - d/m/Y') }}
                    </span>
                    @if($showtime->movie->trailer_url)
                        <button @click.prevent="trailerUrl = '{{ $showtime->movie->trailer_url }}'; showTrailer = true"
                            class="btn-secondary px-3 py-1 text-xs">
                            <i class="fas fa-play text-xs mr-2"></i> Xem Trailer
                        </button>
                    @endif
                </div>

                <div class="text-slate-400 text-sm mb-4 line-clamp-3">
                    {{ $showtime->movie->description }}
                </div>

                <div class="text-xs text-slate-500 space-y-1">
                    <p><strong class="text-slate-300">Đạo diễn:</strong> {{ $showtime->movie->director ?: 'Đang cập nhật' }}
                    </p>
                    <p><strong class="text-slate-300">Diễn viên:</strong> {{ $showtime->movie->actors ?: 'Đang cập nhật' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mb-20 mt-10 relative max-w-4xl mx-auto">
            <div
                class="absolute -top-24 left-1/2 -translate-x-1/2 w-3/4 h-32 bg-primary/20 blur-[100px] rounded-full pointer-events-none">
            </div>
            <div
                class="w-full h-16 bg-gradient-to-b from-slate-200 to-slate-400 rounded-[100%] shadow-[0_-10px_40px_rgba(255,255,255,0.15)] flex items-center justify-center relative overflow-hidden transform perspective-[1000px] rotateX-12">
                <div class="absolute inset-0 bg-gradient-to-b from-white/40 to-transparent"></div>
            </div>
            <p class="text-center text-slate-500 font-bold tracking-[0.5em] mt-4 uppercase text-sm drop-shadow-md">Màn Hình
            </p>
        </div>

        <div x-show="loading" class="text-center py-20">
            <div class="inline-block animate-spin w-12 h-12 border-4 border-primary border-t-transparent rounded-full mb-4">
            </div>
            <p class="text-slate-400 font-medium">Đang tải sơ đồ ghế...</p>
        </div>

        <div x-show="!loading" class="overflow-x-auto pb-12 hide-scrollbar">
            <div class="flex flex-col gap-3 mx-auto w-max min-w-full items-center">
                <template x-for="(rowSeats, rowName) in groupedSeats()" :key="rowName">
                    <div class="flex items-center gap-4 group/row">
                        <div class="w-8 text-center font-bold text-slate-500 group-hover/row:text-primary transition-colors text-lg uppercase"
                            x-text="rowName"></div>

                        <div class="flex gap-2">
                            <template x-for="seat in rowSeats" :key="seat.id || seat.seat_id">
                                <button @click="toggleSeat(seat)"
                                    :disabled="(seat.status !== 'available' && seat.status !== 'trống') && !isSelected(seat.id || seat.seat_id)"
                                    class="h-10 sm:h-11 rounded border-b-4 text-xs font-bold transition-all relative group flex items-center justify-center disabled:cursor-not-allowed"
                                    :class="{
                                            'seat-w-standard': !isCoupleSeat(seat),
                                            'seat-w-couple': isCoupleSeat(seat),
                                            'bg-green-500 border-green-700 text-white z-10': isSelected(seat.id || seat.seat_id),
                                            'bg-red-500 border-red-700 text-white opacity-60': (seat.status !== 'available' && seat.status !== 'trống') && !isSelected(seat.id || seat.seat_id),
                                            'bg-gray-200 border-gray-400 text-gray-800 hover:bg-gray-300': (seat.status === 'available' || seat.status === 'trống') && !isSelected(seat.id || seat.seat_id) && !isVipSeat(seat) && !isCoupleSeat(seat),
                                            'bg-amber-500 border-amber-600 text-white hover:bg-amber-600': (seat.status === 'available' || seat.status === 'trống') && !isSelected(seat.id || seat.seat_id) && isVipSeat(seat),
                                            'bg-fuchsia-500 border-fuchsia-600 text-white hover:bg-fuchsia-600': (seat.status === 'available' || seat.status === 'trống') && !isSelected(seat.id || seat.seat_id) && isCoupleSeat(seat)
                                        }">

                                    <span x-text="seat.number"></span>

                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 px-3 py-2 bg-slate-900 border border-slate-700 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap z-50 transition-all duration-200 shadow-xl transform translate-y-2 group-hover:translate-y-0"
                                        x-show="seat.status === 'available' || seat.status === 'trống' || isSelected(seat.id || seat.seat_id)">
                                        <div class="font-bold text-primary mb-1">Ghế <span
                                                x-text="rowName + seat.number"></span></div>
                                        <div class="text-slate-300" x-text="formatCurrency(seat.price)"></div>
                                        <div
                                            class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-3 h-3 bg-slate-900 border-b border-r border-slate-700 transform rotate-45">
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>

                        <div class="w-8 text-center font-bold text-slate-500 group-hover/row:text-primary transition-colors text-lg uppercase"
                            x-text="rowName"></div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Chú thích sơ đồ ghế — dời xuống đây, ngay dưới sơ đồ, bố cục hàng ngang căn giữa --}}
        <div x-show="!loading"
            class="flex flex-wrap justify-center gap-x-6 gap-y-3 text-sm bg-slate-950/50 p-4 rounded-xl border border-slate-800 max-w-3xl mx-auto mb-20">
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 bg-gray-200 border-b-4 border-gray-400 rounded"></div>
                <span class="text-slate-400 text-xs font-medium">Standard</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 bg-amber-500 border-b-4 border-amber-600 rounded"></div>
                <span class="text-slate-400 text-xs font-medium">VIP</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-6 bg-fuchsia-500 border-b-4 border-fuchsia-600 rounded"></div>
                <span class="text-slate-400 text-xs font-medium">Couple</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 bg-red-500 border-b-4 border-red-700 rounded"></div>
                <span class="text-slate-400 text-xs font-medium">Đã đặt</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 bg-green-500 border-b-4 border-green-700 rounded"></div>
                <span class="text-slate-400 text-xs font-medium">Đang chọn</span>
            </div>
        </div>

        <div class="max-w-4xl mx-auto mb-8 rounded-2xl border border-dark-border bg-dark-card p-5 shadow-xl liquid-glass">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h3 class="text-xl font-bold text-white">Đặt bắp nước & snack</h3>
                    <p class="text-sm text-slate-400">Thêm đồ ăn nhẹ để trải nghiệm xem phim trọn vẹn hơn.</p>
                </div>
                <div class="text-sm font-semibold text-primary">Tạm tính: <span
                        x-text="formatCurrency(foodSubtotal)"></span></div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 mt-5">
                <template x-for="item in snackItems" :key="item.id">
                    <div class="rounded-xl border border-dark-border bg-dark p-4 liquid-glass">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 text-white font-semibold">
                                    <span x-text="item.icon"></span>
                                    <span x-text="item.name"></span>
                                </div>
                                <p class="text-sm text-slate-400 mt-1" x-text="item.description"></p>
                                <p class="text-sm font-semibold text-primary mt-2" x-text="formatCurrency(item.price)"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="removeItem(item)"
                                    class="w-8 h-8 rounded-full border border-slate-700 text-slate-300 hover:bg-slate-800">−</button>
                                <span class="w-8 text-center font-bold text-white" x-text="getItemQuantity(item.id)"></span>
                                <button @click="addItem(item)"
                                    class="w-8 h-8 rounded-full border border-primary/40 bg-primary/10 text-primary hover:bg-primary/20">+</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="error" style="display: none;"
            class="fixed top-24 right-4 bg-red-600 text-white px-6 py-4 rounded-xl shadow-2xl z-50 flex items-center gap-3 border border-red-500 transform transition-all duration-300"
            x-transition:enter="translate-x-full opacity-0" x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="translate-x-full opacity-0">
            <i class="fas fa-exclamation-circle text-xl"></i>
            <span class="font-medium" x-text="error"></span>
        </div>

        <div class="fixed bottom-0 left-0 right-0 bg-slate-900/80 backdrop-blur-xl border-t border-white/10 p-4 shadow-[0_-10px_40px_rgba(0,0,0,0.5)] z-40 transition-transform duration-500"
            :class="selectedSeats.length > 0 ? 'translate-y-0' : 'translate-y-full lg:translate-y-0'">
            <div class="container mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="w-full md:w-auto">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-slate-400 text-sm">Ghế đã chọn</p>
                        <span class="bg-primary/20 text-primary text-xs font-bold px-2 py-0.5 rounded-full"
                            x-text="`${selectedSeats.length}/8`"></span>
                    </div>
                    <div class="font-bold text-white min-h-[28px] flex flex-wrap gap-2 items-center">
                        <span x-show="selectedSeats.length === 0" class="text-slate-500 font-normal italic text-sm">Vui lòng
                            chọn ghế trên sơ đồ</span>
                        <template x-for="seat in selectedSeats" :key="seat.id || seat.seat_id">
                            <span
                                class="inline-block bg-white/10 border border-white/20 px-2 py-1 rounded text-sm text-white"
                                x-text="`${seat.row_letter}${seat.number}`"></span>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-between md:justify-end gap-6 w-full md:w-auto">
                    <div class="text-left md:text-right">
                        <p class="text-slate-400 text-sm mb-0.5">Tổng thanh toán</p>
                        <p class="text-3xl font-black text-primary drop-shadow-md" x-text="formatCurrency(totalPrice)"></p>
                    </div>

                    <button @click="submitBooking()" :disabled="selectedSeats.length === 0 || processing"
                        class="btn-primary group disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!processing" class="text-lg">Thanh toán</span>
                        <i x-show="!processing"
                            class="fas fa-arrow-right group-hover:translate-x-1 transition-transform ml-2"></i>
                        <span x-show="processing"
                            class="inline-block animate-spin w-5 h-5 border-2 border-white/30 border-t-white rounded-full"></span>
                        <span x-show="processing">Đang xử lý...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .seat-w-standard {
            width: 2.5rem;
        }

        .seat-w-couple {
            width: 5.5rem;
        }

        @media (min-width: 640px) {
            .seat-w-standard {
                width: 2.75rem;
            }

            .seat-w-couple {
                width: 6rem;
            }
        }

        .hide-scrollbar::-webkit-scrollbar {
            height: 8px;
        }

        .hide-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 4px;
        }

        .hide-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(51, 65, 85, 0.8);
            border-radius: 4px;
        }

        .hide-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(71, 85, 105, 1);
        }
    </style>

    <script>
        function seatSelector() {
            return {
                showtimeId: {{ $showtime->id }},
                seats: [],
                selectedSeats: [],
                selectedItems: [],
                snackItems: [
                    { id: 'popcorn_small', name: 'Bắp rang nhỏ', price: 35000, icon: '🍿', description: 'Phù hợp cho một người' },
                    { id: 'popcorn_large', name: 'Bắp rang lớn', price: 55000, icon: '🍿', description: 'Cặp đôi hoặc nhóm xem phim' },
                    { id: 'soda_small', name: 'Nước ngọt nhỏ', price: 25000, icon: '🥤', description: 'Ly nhỏ, giải khát nhanh' },
                    { id: 'soda_large', name: 'Nước ngọt lớn', price: 35000, icon: '🥤', description: 'Ly lớn, uống thoải mái' },
                    { id: 'snack_combo', name: 'Combo snack', price: 65000, icon: '🍿', description: 'Bắp + nước + snack' },
                ],
                loading: true,
                processing: false,
                error: '',
                pollInterval: null,

                init() {
                    this.fetchSeats();
                    this.pollInterval = setInterval(() => this.fetchSeats(false), 10000);
                },

                async fetchSeats(showLoader = true) {
                    if (showLoader) this.loading = true;
                    try {
                        const res = await fetch(`/api/showtimes/${this.showtimeId}/seats`);
                        const data = await res.json();
                        this.seats = data.data;

                        console.log("CẤU TRÚC GHẾ THẬT:", this.seats[0]);

                        this.selectedSeats = this.selectedSeats.filter(sel => {
                            const selId = sel.id || sel.seat_id;
                            const latest = this.seats.find(s => (s.id || s.seat_id) === selId);
                            return latest && (latest.status === 'available' || latest.status === 'trống');
                        });
                    } catch (err) {
                        console.error("Failed to fetch seats", err);
                    } finally {
                        this.loading = false;
                    }
                },

                // GOM NHÓM VÀ TỰ ĐỘNG CHIA MA TRẬN 10 GHẾ/HÀNG KHI BACKEND TRẢ VỀ NULL
                groupedSeats() {
                    const grouped = {};
                    if (!this.seats || this.seats.length === 0) return grouped;

                    // Cấu hình chuẩn theo backend: 8 hàng (A -> H)
                    const rowLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                    // ĐỔI TỪ 10 THÀNH 12: Cố định 12 ghế mỗi hàng theo ma trận chuẩn của bạn
                    const seatsPerRow = 12;

                    this.seats.forEach((seat, index) => {
                        // Thuật toán tính toán ma trận tự động dựa trên mảng phẳng của Backend
                        let rowName = seat.row || seat.seat_row || (seat.seat ? (seat.seat.seat_row || seat.seat.row) : null);

                        if (!rowName) {
                            // Lấy index chia cho 12 để xác định hàng chính xác
                            const rowIndex = Math.floor(index / seatsPerRow);
                            rowName = rowLetters[rowIndex] || `Hàng ${rowIndex + 1}`;
                        }

                        // Tự động gán ký tự hàng và số ghế cục bộ từ 1 đến 12 để đồng bộ hiển thị
                        seat.row_letter = rowName;
                        if (!seat.number) {
                            seat.number = (index % seatsPerRow) + 1;
                        }

                        if (!grouped[rowName]) {
                            grouped[rowName] = [];
                        }
                        grouped[rowName].push(seat);
                    });

                    // Sắp xếp các ghế trong hàng chạy từ 1 đến 12 tăng dần
                    const sortedGroups = {};
                    Object.keys(grouped).sort().forEach(key => {
                        sortedGroups[key] = grouped[key].sort((a, b) => parseInt(a.number) - parseInt(b.number));
                    });

                    return sortedGroups;
                },

                toggleSeat(seat) {
                    if (!seat) return;
                    const currentId = seat.id || seat.seat_id;
                    const isAvail = seat.status === 'available' || seat.status === 'trống';
                    if (!isAvail && !this.isSelected(currentId)) return;

                    const idx = this.selectedSeats.findIndex(s => (s.id || s.seat_id) === currentId);
                    if (idx > -1) {
                        this.selectedSeats.splice(idx, 1);
                    } else {
                        if (this.selectedSeats.length >= 8) {
                            this.showError('Bạn chỉ có thể chọn tối đa 8 ghế cho mỗi giao dịch.');
                            return;
                        }
                        this.selectedSeats.push(seat);
                    }
                },

                isSelected(seatId) {
                    return this.selectedSeats.some(s => (s.id || s.seat_id) === seatId);
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

                isVipSeat(seat) {
                    return (seat?.type || seat?.seat_type || seat?.seat?.seat_type || '').toLowerCase() === 'vip';
                },

                isCoupleSeat(seat) {
                    return (seat?.type || seat?.seat_type || seat?.seat?.seat_type || '').toLowerCase() === 'couple';
                },

                get foodSubtotal() {
                    return this.selectedItems.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.quantity || 0), 0);
                },

                get totalPrice() {
                    return this.selectedSeats.reduce((sum, seat) => sum + Number(seat.price || 0), 0) + this.foodSubtotal;
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
                },

                showError(msg) {
                    this.error = msg;
                    setTimeout(() => this.error = '', 4000);
                },

                async submitBooking() {
                    if (this.selectedSeats.length === 0) return;
                    this.processing = true;

                    try {
                        const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                        const targetSeatIds = this.selectedSeats.map(s => s.seat_id || s.id);

                        const response = await fetch('/bookings/hold', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                showtime_id: this.showtimeId,
                                seat_ids: targetSeatIds,
                                items: this.selectedItems.map(({ id, quantity }) => ({ id, quantity }))
                            })
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            if (response.status === 409) {
                                this.showError(result.message || 'Rất tiếc, một số ghế bạn chọn vừa được người khác đặt. Vui lòng chọn lại.');
                                await this.fetchSeats(false);
                                this.selectedSeats = [];
                            } else if (response.status === 401) {
                                window.location.href = '/login';
                            } else {
                                this.showError('Có lỗi xảy ra: ' + (result.message || response.statusText));
                            }
                            return;
                        }

                        window.location.href = '/checkout/' + result.booking_id;

                    } catch (err) {
                        console.error(err);
                        this.showError('Không thể kết nối đến máy chủ. Vui lòng kiểm tra lại mạng.');
                    } finally {
                        this.processing = false;
                    }
                }
            }
        }
    </script>
@endsection