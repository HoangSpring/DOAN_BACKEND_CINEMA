@extends('layouts.staff')

@section('content')
<div class="max-w-2xl mx-auto" x-data="checkinScanner()">
    <div class="bg-white shadow-xl rounded-xl p-8 text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-4">Check-in Điện Tử</h2>
        
        <div id="reader" class="mx-auto overflow-hidden rounded mb-6" style="max-width: 500px; border: 2px solid #e5e7eb;"></div>
        
        <div class="mt-4 flex gap-2">
            <input type="text" x-model="manualCode" placeholder="Quét không được? Nhập mã JSON giả lập tại đây" class="w-full border p-4 rounded text-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button @click="processCheckin(manualCode)" class="px-6 py-4 bg-gray-800 text-white font-bold rounded hover:bg-gray-700">Check</button>
        </div>

        <!-- Bảng thông báo TO RÕ -->
        <div x-show="result" class="mt-8 p-6 rounded-xl shadow-inner transition" :class="isSuccess ? 'bg-green-500' : 'bg-red-500'" style="display: none;">
            <h3 class="text-3xl font-black text-white uppercase tracking-widest mb-2" x-text="isSuccess ? 'THÀNH CÔNG' : 'TỪ CHỐI'"></h3>
            <p class="text-xl text-white font-medium" x-text="message"></p>
        </div>
    </div>

    <!-- Cài đặt thư viện html5-qrcode qua CDN -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkinScanner', () => ({
                manualCode: '',
                result: false,
                isSuccess: false,
                message: '',
                isScanning: false,

                init() {
                    this.startScanner();
                },

                startScanner() {
                    const html5QrCode = new Html5Qrcode("reader");
                    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
                    
                    html5QrCode.start(
                        { facingMode: "environment" }, 
                        config,
                        (decodedText) => {
                            if (!this.isScanning) {
                                this.processCheckin(decodedText);
                            }
                        },
                        (errorMessage) => {
                            // ignore silent errors during scanning
                        }
                    ).catch(err => {
                        console.error("Camera error:", err);
                    });
                },

                async processCheckin(qrDataStr) {
                    if (!qrDataStr) return;
                    this.isScanning = true;
                    this.result = false;
                    
                    try {
                        const response = await fetch('/api/staff/checkin', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ qr_data: qrDataStr })
                        });
                        
                        const data = await response.json();
                        
                        this.result = true;
                        if (response.ok) {
                            this.isSuccess = true;
                            this.message = "VÉ HỢP LỆ. MỜI VÀO RẠP.";
                        } else {
                            this.isSuccess = false;
                            this.message = data.message || "Lỗi không xác định";
                        }
                    } catch (e) {
                        this.result = true;
                        this.isSuccess = false;
                        this.message = "Lỗi mạng hoặc không thể kết nối tới server.";
                    }

                    // Tự reset sau 3 giây
                    setTimeout(() => {
                        this.result = false;
                        this.isScanning = false;
                        this.manualCode = '';
                    }, 3000);
                }
            }));
        });
    </script>
</div>
@endsection
