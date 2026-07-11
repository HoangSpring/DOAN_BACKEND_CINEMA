<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\Payment;
use App\Http\Requests\HoldSeatsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function hold(HoldSeatsRequest $request)
    {
        $validated = $request->validated();
        $showtimeId = $validated['showtime_id'];
        $seatIds = $validated['seat_ids'];
        $userId = Auth::id();

        try {
            DB::beginTransaction();

            $showtime = Showtime::findOrFail($showtimeId);

            $showtimeSeats = ShowtimeSeat::with('seat')
                ->where('showtime_id', $showtimeId)
                ->whereIn('seat_id', $seatIds)
                ->lockForUpdate()
                ->get();

            if ($showtimeSeats->count() !== count($seatIds)) {
                DB::rollBack();
                return response()->json([
                    'error_code' => 'VALIDATION_ERROR',
                    'message' => 'Some seats do not belong to this showtime'
                ], 422);
            }

            $totalAmount = 0;
            $seatsToUpdate = [];

            foreach ($showtimeSeats as $stSeat) {
                if ($stSeat->status === 'booked') {
                    DB::rollBack();
                    return response()->json([
                        'error_code' => 'SEAT_ALREADY_BOOKED',
                        'message' => "Seat {$stSeat->seat_id} is already booked"
                    ], 409);
                }

                if ($stSeat->status === 'holding' && $stSeat->hold_expires_at && Carbon::now()->lessThan($stSeat->hold_expires_at)) {
                    DB::rollBack();
                    return response()->json([
                        'error_code' => 'SEAT_ALREADY_HELD',
                        'message' => "Seat {$stSeat->seat_id} is currently held by someone else"
                    ], 409);
                }

                $seatsToUpdate[] = $stSeat->id;

                $price = $stSeat->seat->type === 'vip' ? $showtime->price_vip : $showtime->price_standard;
                $totalAmount += $price;
            }

            $expiresAt = Carbon::now()->addMinutes(5);

            ShowtimeSeat::whereIn('id', $seatsToUpdate)->update([
                'status' => 'holding',
                'held_by_user_id' => $userId,
                'hold_expires_at' => $expiresAt
            ]);

            $bookingCode = 'BK' . date('YmdHis') . strtoupper(substr(uniqid(), -4));

            $booking = Booking::create([
                'user_id' => $userId,
                'showtime_id' => $showtimeId,
                'booking_code' => $bookingCode,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'booking_type' => 'online',
            ]);

            foreach ($showtimeSeats as $stSeat) {
                $price = $stSeat->seat->type === 'vip' ? $showtime->price_vip : $showtime->price_standard;
                BookingSeat::create([
                    'booking_id' => $booking->id,
                    'showtime_seat_id' => $stSeat->id,
                    'price' => $price,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Seats held successfully',
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'seat_ids' => $seatIds,
                'hold_expires_at' => $expiresAt
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Hold seats failed: ' . $e->getMessage(), ['exception' => $e, 'showtime_id' => $showtimeId, 'seat_ids' => $seatIds, 'user_id' => $userId]);
            return response()->json([
                'error_code' => 'INTERNAL_ERROR',
                'message' => 'Đã có lỗi xảy ra, vui lòng thử lại.'
            ], 500);
        }
    }

    public function checkout(Request $request, Booking $booking)
    {
        try {
            \Illuminate\Support\Facades\Gate::authorize('checkout', $booking);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'error_code' => 'FORBIDDEN',
                'message' => 'Bạn không có quyền thực hiện hành động này.'
            ], 403);
        }

        $paymentUrl = url('/api/payments/simulate-gateway?booking_id=' . $booking->id);

        return response()->json([
            'payment_url' => $paymentUrl,
            'idempotency_key' => $request->header('Idempotency-Key')
        ]);
    }

    public function counter(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|exists:showtimes,id',
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'exists:seats,id',
            'payment_method' => 'required|in:cash'
        ]);

        $idempotencyKey = $request->header('Idempotency-Key');

        try {
            DB::beginTransaction();

            $existingPayment = Payment::where('idempotency_key', $idempotencyKey)->first();
            if ($existingPayment) {
                DB::rollBack();
                return response()->json([
                    'error_code' => 'PAYMENT_ALREADY_PROCESSED',
                    'message' => 'Idempotent request',
                    'booking_id' => $existingPayment->booking_id
                ], 200);
            }

            $showtimeId = $request->showtime_id;
            $seatIds = $request->seat_ids;

            $showtime = Showtime::findOrFail($showtimeId);

            $showtimeSeats = ShowtimeSeat::with('seat')
                ->where('showtime_id', $showtimeId)
                ->whereIn('seat_id', $seatIds)
                ->lockForUpdate()
                ->get();

            if ($showtimeSeats->count() !== count($seatIds)) {
                DB::rollBack();
                return response()->json(['error_code' => 'VALIDATION_ERROR', 'message' => 'Invalid seats'], 422);
            }

            $totalAmount = 0;
            $seatsToUpdate = [];

            foreach ($showtimeSeats as $stSeat) {
                if ($stSeat->status === 'booked') {
                    DB::rollBack();
                    return response()->json(['error_code' => 'SEAT_ALREADY_BOOKED', 'message' => "Seat {$stSeat->seat_id} is already booked"], 409);
                }
                $seatsToUpdate[] = $stSeat->id;
                $price = $stSeat->seat->type === 'vip' ? $showtime->price_vip : $showtime->price_standard;
                $totalAmount += $price;
            }

            ShowtimeSeat::whereIn('id', $seatsToUpdate)->update(['status' => 'booked']);

            $bookingCode = 'BK' . date('YmdHis') . strtoupper(substr(uniqid(), -4));

            $booking = Booking::create([
                'user_id' => null,
                'staff_id' => Auth::id(),
                'showtime_id' => $showtimeId,
                'booking_code' => $bookingCode,
                'total_amount' => $totalAmount,
                'status' => 'paid',
                'booking_type' => 'counter',
            ]);

            foreach ($showtimeSeats as $stSeat) {
                $price = $stSeat->seat->type === 'vip' ? $showtime->price_vip : $showtime->price_standard;
                BookingSeat::create([
                    'booking_id' => $booking->id,
                    'showtime_seat_id' => $stSeat->id,
                    'price' => $price,
                ]);
            }

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'idempotency_key' => $idempotencyKey,
                'amount' => $totalAmount,
                'payment_method' => 'cash',
                'status' => 'success',
                'paid_at' => Carbon::now()
            ]);

            $payload = json_encode([
                'booking_id' => $booking->id,
                'code' => $bookingCode,
            ]);
            $signature = hash_hmac('sha256', $payload, env('QR_SECRET_KEY', 'secret'));
            $qrData = json_encode(['payload' => json_decode($payload), 'sig' => $signature]);

            $booking->update(['qr_code_data' => $qrData]);

            DB::commit();

            return response()->json([
                'message' => 'Ticket issued successfully',
                'booking' => $booking
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Counter booking failed: ' . $e->getMessage(), ['exception' => $e, 'showtime_id' => $request->showtime_id ?? null, 'seat_ids' => $request->seat_ids ?? null, 'staff_id' => Auth::id()]);
            return response()->json(['error_code' => 'INTERNAL_ERROR', 'message' => 'Đã có lỗi xảy ra, vui lòng thử lại.'], 500);
        }
    }
}
