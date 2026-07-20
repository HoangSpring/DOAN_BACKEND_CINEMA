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

    // Booking Web Routes (No /api/ prefix to avoid conflict with Postman, uses web session auth)
    Route::post('/bookings/hold', [\App\Http\Controllers\BookingController::class, 'hold'])->name('web.bookings.hold');
    Route::post('/bookings/{booking}/checkout', [\App\Http\Controllers\BookingController::class, 'checkout'])
        ->middleware(\App\Http\Middleware\CheckIdempotencyKey::class);

    // Prompt 14 routes
    Route::get('/checkout/{booking}', [WebController::class, 'checkout'])->name('checkout');
    Route::get('/tickets/{booking}', [WebController::class, 'ticket'])->name('tickets.show');
    Route::get('/my-tickets', [WebController::class, 'myTickets'])->name('my-tickets');

    // User Profile
    Route::get('/profile', [WebController::class, 'profile'])->name('profile');
    Route::put('/profile', [WebController::class, 'updateProfile'])->name('profile.update');
});

// Admin Web Routes (Prompt 16)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Web\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [\App\Http\Controllers\Web\Admin\DashboardController::class, 'index']);
    Route::get('reports', [\App\Http\Controllers\Web\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export/date', [\App\Http\Controllers\Web\Admin\ReportController::class, 'exportRevenueByDate'])->name('reports.export.date');
    Route::get('reports/export/movie', [\App\Http\Controllers\Web\Admin\ReportController::class, 'exportRevenueByMovie'])->name('reports.export.movie');
    Route::resource('users', \App\Http\Controllers\Web\Admin\UserController::class);
    Route::resource('movies', \App\Http\Controllers\Web\Admin\MovieController::class);
    Route::resource('tags', \App\Http\Controllers\Web\Admin\TagController::class)->except(['create', 'show', 'edit']);
    Route::resource('rooms', \App\Http\Controllers\Web\Admin\RoomController::class)->only(['index', 'show']);
    Route::put('rooms/{room}/seats/{seat}', [\App\Http\Controllers\Web\Admin\RoomController::class, 'updateSeat'])->name('rooms.update-seat');
    Route::resource('showtimes', \App\Http\Controllers\Web\Admin\ShowtimeController::class)->only(['index', 'create', 'store']);
    Route::post('showtimes/auto-generate', [\App\Http\Controllers\Admin\AdminShowtimeController::class, 'autoGenerate'])->name('showtimes.auto-generate');
});

// Staff Web Routes (Prompt 18)
Route::middleware(['auth', 'role:admin,staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('counter', [\App\Http\Controllers\Web\StaffController::class, 'counter'])->name('counter');
    Route::get('checkin', [\App\Http\Controllers\Web\StaffController::class, 'checkin'])->name('checkin');

    // Booking actions for Staff Counter (Web session)
    Route::post('bookings/counter', [\App\Http\Controllers\BookingController::class, 'counter'])
        ->middleware(\App\Http\Middleware\CheckIdempotencyKey::class)->name('bookings.counter');
    Route::post('bookings/{booking}/cancel', [\App\Http\Controllers\BookingController::class, 'cancel'])->name('bookings.cancel');
});
