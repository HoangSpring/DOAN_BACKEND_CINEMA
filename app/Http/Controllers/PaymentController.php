<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\ShowtimeSeat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function simulate(Request $request)
    {
        $bookingId = $request->query('booking_id');
        
        if (!$bookingId) {
            return response()->json(['error' => 'Missing booking_id'], 400);
        }

        $booking = Booking::find($bookingId);
        
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // Tự động gọi callback để hoàn tất thanh toán
        $request->merge([
            'idempotency_key' => 'sim_' . time() . '_' . $bookingId,
            'booking_id' => $bookingId,
            'amount' => $booking->total_amount
        ]);
        
        $response = $this->callback($request);
        
        if ($response->status() == 200) {
            // Chuyển hướng người dùng về trang xem vé
            return redirect()->route('tickets.show', ['booking' => $bookingId]);
        }
        
        return $response;
    }

    public function callback(Request $request)
    {
        $idempotencyKey = $request->input('idempotency_key');
        $bookingId = $request->input('booking_id');
        $amount = $request->input('amount');

        if (!$idempotencyKey) {
            return response()->json(['error_code' => 'MISSING_IDEMPOTENCY_KEY'], 400);
        }

        try {
            DB::beginTransaction();

            $payment = Payment::where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();

            if ($payment && $payment->status === 'success') {
                DB::rollBack();
                return response()->json([
                    'error_code' => 'PAYMENT_ALREADY_PROCESSED',
                    'message' => 'Payment already processed'
                ], 200);
            }

            if (!$payment) {
                $payment = Payment::create([
                    'booking_id' => $bookingId,
                    'idempotency_key' => $idempotencyKey,
                    'amount' => $amount,
                    'payment_method' => 'online_gateway',
                    'status' => 'pending'
                ]);
            }

            $booking = Booking::findOrFail($bookingId);

            if ($booking->status !== 'pending') {
                DB::rollBack();
                return response()->json(['error_code' => 'INVALID_BOOKING_STATUS'], 400);
            }

            $payment->update([
                'status' => 'success',
                'paid_at' => Carbon::now()
            ]);

            $booking->update(['status' => 'paid']);

            $showtimeSeatIds = $booking->bookingSeats()->pluck('showtime_seat_id');
            ShowtimeSeat::whereIn('id', $showtimeSeatIds)->update(['status' => 'booked']);

            $payload = json_encode([
                'booking_id' => $booking->id,
                'code' => $booking->booking_code,
            ]);
            $hmacSignature = hash_hmac('sha256', $payload, env('QR_SECRET_KEY', 'secret'));
            $qrData = json_encode(['payload' => json_decode($payload), 'sig' => $hmacSignature]);

            $booking->update(['qr_code_data' => $qrData]);

            DB::commit();

            return response()->json([
                'message' => 'Payment successful, ticket issued.',
                'booking' => $booking,
                'qr_data' => $qrData
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Payment callback failed: ' . $e->getMessage(), ['exception' => $e, 'booking_id' => $bookingId]);
            return response()->json(['error_code' => 'INTERNAL_ERROR', 'message' => 'Đã có lỗi xảy ra, vui lòng thử lại.'], 500);
        }
    }
}
