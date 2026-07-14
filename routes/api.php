<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ShowtimeController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public routes
Route::get('movies', [MovieController::class, 'index']);
Route::get('tags', [TagController::class, 'index']);
Route::get('movies/{movie}/showtimes', [ShowtimeController::class, 'index']);
Route::get('showtimes/{showtime}/seats', [ShowtimeController::class, 'seats']);

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Booking routes
    Route::post('/bookings/hold', [\App\Http\Controllers\BookingController::class, 'hold']);
    Route::post('/bookings/{booking}/checkout', [\App\Http\Controllers\BookingController::class, 'checkout'])
        ->middleware(\App\Http\Middleware\CheckIdempotencyKey::class);



    // Admin routes
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

    // Staff routes
    Route::middleware('role:admin,staff')->prefix('staff')->group(function () {
        Route::post('/bookings/counter', [\App\Http\Controllers\BookingController::class, 'counter'])
            ->middleware(\App\Http\Middleware\CheckIdempotencyKey::class);
        Route::post('/bookings/{booking}/cancel', [\App\Http\Controllers\BookingController::class, 'cancel']);
            
        Route::post('/checkin', [\App\Http\Controllers\CheckinController::class, 'checkin']);
    });
});

// Webhook callback (usually public, signature validated)
Route::post('/payments/callback', [\App\Http\Controllers\PaymentController::class, 'callback']);
Route::any('/payments/simulate-gateway', [\App\Http\Controllers\PaymentController::class, 'simulate']);
