@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-700">Sơ đồ ghế: {{ $room->name }}</h1>
    <a href="{{ route('admin.rooms.index') }}" class="text-gray-600 hover:text-gray-900">Quay lại danh sách</a>
</div>

<div class="bg-white rounded-lg shadow p-6" x-data="seatManager()">
    <!-- Screen -->
    <div class="mb-12">
        <div class="w-full max-w-2xl mx-auto h-8 bg-gray-300 rounded-t-3xl shadow-inner text-center text-sm font-bold text-gray-500 leading-8">MÀN HÌNH</div>
        <div class="w-full max-w-3xl mx-auto h-12 bg-gradient-to-b from-gray-100 to-transparent opacity-50"></div>
    </div>

    <!-- Seating Grid -->
    @php
        $groupedSeats = $seats->groupBy('seat_row');
    @endphp

    <div class="overflow-x-auto">
        <div class="inline-block min-w-full text-center space-y-4">
            @foreach($groupedSeats as $row => $rowSeats)
                <div class="flex justify-center items-center gap-2">
                    <div class="w-8 font-bold text-gray-500">{{ $row }}</div>
                    <div class="flex gap-2">
                        @foreach($rowSeats as $seat)
                            <div class="relative">
                                <button 
                                    @click="openPopover({{ $seat->id }}, '{{ $seat->seat_row }}{{ $seat->seat_number }}', '{{ $seat->seat_type }}')"
                                    id="seat-btn-{{ $seat->id }}"
                                    class="w-10 h-10 rounded-t-lg border-b-4 border-black/20 text-xs font-bold transition flex items-center justify-center text-white"
                                    :class="{
                                        'bg-gray-400 hover:bg-gray-500': getSeatType({{ $seat->id }}, '{{ $seat->seat_type }}') === 'standard',
                                        'bg-red-500 hover:bg-red-600': getSeatType({{ $seat->id }}, '{{ $seat->seat_type }}') === 'vip',
                                        'bg-pink-500 hover:bg-pink-600': getSeatType({{ $seat->id }}, '{{ $seat->seat_type }}') === 'couple'
                                    }"
                                >
                                    {{ $seat->seat_number }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div class="w-8 font-bold text-gray-500">{{ $row }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-12 flex justify-center gap-6 border-t pt-6">
        <div class="flex items-center gap-2"><div class="w-6 h-6 bg-gray-400 rounded-t border-b-2 border-black/20"></div><span class="text-sm">Standard</span></div>
        <div class="flex items-center gap-2"><div class="w-6 h-6 bg-red-500 rounded-t border-b-2 border-black/20"></div><span class="text-sm">VIP</span></div>
        <div class="flex items-center gap-2"><div class="w-6 h-6 bg-pink-500 rounded-t border-b-2 border-black/20"></div><span class="text-sm">Couple</span></div>
    </div>

    <!-- Popover for changing seat type -->
    <div x-show="popoverOpen" @click.away="popoverOpen = false" class="fixed z-50 bg-white border border-gray-200 shadow-xl rounded-lg p-4 w-64 transition-all" :style="popoverStyle" style="display: none;">
        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2">Ghế <span x-text="activeSeatName"></span></h3>
        <p class="text-xs text-gray-500 mb-2">Đổi loại ghế:</p>
        <div class="space-y-2">
            <button @click="changeType('standard')" class="w-full text-left px-3 py-2 text-sm rounded bg-gray-100 hover:bg-gray-200" :class="{'ring-2 ring-indigo-500': activeSeatType === 'standard'}">Standard</button>
            <button @click="changeType('vip')" class="w-full text-left px-3 py-2 text-sm rounded bg-red-100 hover:bg-red-200 text-red-800" :class="{'ring-2 ring-indigo-500': activeSeatType === 'vip'}">VIP</button>
            <button @click="changeType('couple')" class="w-full text-left px-3 py-2 text-sm rounded bg-pink-100 hover:bg-pink-200 text-pink-800" :class="{'ring-2 ring-indigo-500': activeSeatType === 'couple'}">Couple</button>
        </div>
        <div x-show="error" class="mt-3 text-xs text-red-500 font-medium" x-text="error"></div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('seatManager', () => ({
            popoverOpen: false,
            activeSeatId: null,
            activeSeatName: '',
            activeSeatType: '',
            popoverStyle: '',
            error: '',
            seatTypes: {}, // Client-side state cache

            getSeatType(id, initialType) {
                return this.seatTypes[id] || initialType;
            },

            openPopover(id, name, type) {
                this.error = '';
                this.activeSeatId = id;
                this.activeSeatName = name;
                this.activeSeatType = this.getSeatType(id, type);
                
                const btn = document.getElementById('seat-btn-' + id);
                const rect = btn.getBoundingClientRect();
                
                // Position popover
                this.popoverStyle = `top: ${rect.bottom + window.scrollY + 10}px; left: ${rect.left + window.scrollX - 100 + rect.width/2}px;`;
                this.popoverOpen = true;
            },

            async changeType(newType) {
                if (this.activeSeatType === newType) {
                    this.popoverOpen = false;
                    return;
                }
                
                this.error = '';
                
                try {
                    const response = await fetch(`/admin/rooms/{{ $room->id }}/seats/${this.activeSeatId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ seat_type: newType })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        this.seatTypes[this.activeSeatId] = newType;
                        this.popoverOpen = false;
                    } else {
                        this.error = data.error || 'Có lỗi xảy ra.';
                    }
                } catch (e) {
                    this.error = 'Lỗi kết nối. Vui lòng thử lại sau.';
                }
            }
        }));
    });
</script>
@endsection
