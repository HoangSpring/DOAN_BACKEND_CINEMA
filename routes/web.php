<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

Route::get('/', [WebController::class, 'index'])->name('home');
Route::get('/movies/{movie}', [WebController::class, 'showMovie'])->name('movies.show');

Route::get('/login', [WebController::class, 'loginForm'])->name('login');
Route::post('/login', [WebController::class, 'loginSubmit']);
Route::get('/register', [WebController::class, 'registerForm'])->name('register');
Route::post('/register', [WebController::class, 'registerSubmit']);
Route::post('/logout', [WebController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/showtimes/{showtime}/seats', [WebController::class, 'seats'])->name('showtimes.seats');
    Route::get('/api/showtimes/{showtime}/seats', [WebController::class, 'seatsApi'])->name('api.showtimes.seats');
    Route::post('/api/bookings/hold', [\App\Http\Controllers\BookingController::class, 'hold'])->name('bookings.hold');
    Route::post('/api/bookings/{booking}/checkout', [\App\Http\Controllers\BookingController::class, 'checkout'])
        ->middleware(\App\Http\Middleware\CheckIdempotencyKey::class);
    
    // Prompt 14 routes
    Route::get('/checkout/{booking}', [WebController::class, 'checkout'])->name('checkout');
    Route::get('/tickets/{booking}', [WebController::class, 'ticket'])->name('tickets.show');
    Route::get('/my-tickets', [WebController::class, 'myTickets'])->name('my-tickets');
});

// Admin Web Routes (Prompt 16)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Web\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [\App\Http\Controllers\Web\Admin\DashboardController::class, 'index']);
    Route::get('reports', [\App\Http\Controllers\Web\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::resource('users', \App\Http\Controllers\Web\Admin\UserController::class);
    Route::resource('movies', \App\Http\Controllers\Web\Admin\MovieController::class);
    Route::resource('tags', \App\Http\Controllers\Web\Admin\TagController::class)->except(['create', 'show', 'edit']);
    Route::resource('rooms', \App\Http\Controllers\Web\Admin\RoomController::class)->only(['index', 'show']);
    Route::put('rooms/{room}/seats/{seat}', [\App\Http\Controllers\Web\Admin\RoomController::class, 'updateSeat'])->name('rooms.update-seat');
    Route::resource('showtimes', \App\Http\Controllers\Web\Admin\ShowtimeController::class)->only(['index', 'create', 'store']);
});

// Staff Web Routes (Prompt 18)
Route::middleware(['auth', 'role:admin,staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('counter', [\App\Http\Controllers\Web\StaffController::class, 'counter'])->name('counter');
    Route::get('checkin', [\App\Http\Controllers\Web\StaffController::class, 'checkin'])->name('checkin');
});
