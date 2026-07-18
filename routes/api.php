<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ShowtimeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebController;

// =========================================================================
// 1. CÁC ROUTE CÔNG KHAI THUỘC NHÓM API (Public API Routes)
// =========================================================================
Route::middleware('api')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('movies', [MovieController::class, 'index']);
    Route::get('tags', [TagController::class, 'index']);
    Route::get('movies/{movie}/showtimes', [ShowtimeController::class, 'index']);

    // [SỬA 3]: Trả route xem ghế về nhóm 'api' để không bị gán nhầm 'web'
    // Giữ đúng WebController@seatsApi và name gốc theo danh sách route của bạn
    Route::get('showtimes/{showtime}/seats', [WebController::class, 'seatsApi'])->name('api.showtimes.seats');
});

// =========================================================================
// 2. CÁC ROUTE BẮT BUỘC ĐĂNG NHẬP (Sử dụng Token Sanctum)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // [SỬA 1]: Route giữ ghế chính thức ăn cấu hình auth:sanctum stateless
    Route::post('/bookings/hold', [BookingController::class, 'hold'])->name('bookings.hold');

    // [SỬA 2]: Route checkout ăn cấu hình auth:sanctum phối hợp cùng IdempotencyKey
    Route::post('/bookings/{booking}/checkout', [BookingController::class, 'checkout'])
        ->middleware(\App\Http\Middleware\CheckIdempotencyKey::class);

    // Admin routes (Yêu cầu quyền admin)
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::apiResource('movies', \App\Http\Controllers\Admin\AdminMovieController::class)->except(['index', 'show']);
        Route::apiResource('tags', \App\Http\Controllers\Admin\AdminTagController::class);
        Route::post('rooms', [\App\Http\Controllers\Admin\AdminRoomController::class, 'store']);
        Route::put('rooms/{room}/seats/{seat}', [\App\Http\Controllers\Admin\AdminRoomController::class, 'updateSeat']);
        Route::post('showtimes', [\App\Http\Controllers\Admin\AdminShowtimeController::class, 'store']);

        // Reports
        Route::get('reports/revenue', [\App\Http\Controllers\Admin\AdminReportController::class, 'revenue']);
        Route::get('reports/occupancy', [\App\Http\Controllers\Admin\AdminReportController::class, 'occupancy']);
        Route::get('reports/revenue-by-movie', [\App\Http\Controllers\Admin\AdminReportController::class, 'revenueByMovie']);
    });

    // Staff routes (Yêu cầu quyền admin hoặc staff)
    Route::middleware('role:admin,staff')->prefix('staff')->group(function () {
        Route::post('/bookings/counter', [BookingController::class, 'counter'])
            ->middleware(\App\Http\Middleware\CheckIdempotencyKey::class);
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

        Route::post('/checkin', [CheckinController::class, 'checkin']);
    });
});

// =========================================================================
// 3. WEBHOOK CALLBACK CÔNG KHAI
// =========================================================================
Route::middleware('api')->group(function () {
    Route::post('/payments/callback', [PaymentController::class, 'callback']);
    Route::any('/payments/simulate-gateway', [PaymentController::class, 'simulate']);
});