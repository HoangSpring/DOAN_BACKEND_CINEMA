<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;

class CheckinController extends Controller
{
    public function checkin(Request $request)
    {
        $qrDataStr = $request->input('qr_data');
        if (!$qrDataStr) {
            return response()->json(['error_code' => 'INVALID_QR_SIGNATURE', 'message' => 'Missing QR Data'], 400);
        }

        $qrData = is_string($qrDataStr) ? json_decode($qrDataStr, true) : $qrDataStr;
        if (!$qrData || !isset($qrData['payload']) || !isset($qrData['sig'])) {
            return response()->json(['error_code' => 'INVALID_QR_SIGNATURE', 'message' => 'Invalid QR Data format'], 400);
        }

        $payload = $qrData['payload'];
        $signature = $qrData['sig'];

        $expectedSignature = hash_hmac('sha256', json_encode($payload), env('QR_SECRET_KEY', 'secret'));
        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error_code' => 'INVALID_QR_SIGNATURE', 'message' => 'Signature mismatch'], 400);
        }

        $bookingId = $payload['booking_id'] ?? null;
        if (!$bookingId) {
            return response()->json(['error_code' => 'INVALID_QR_SIGNATURE', 'message' => 'Invalid booking ID in QR'], 400);
        }

        $booking = Booking::with(['showtime.movie', 'bookingSeats.showtimeSeat.seat'])->find($bookingId);
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        if ($booking->status !== 'paid') {
            return response()->json(['error_code' => 'VALIDATION_ERROR', 'message' => 'Vé chưa thanh toán hoặc đã huỷ'], 422);
        }

        if ($booking->is_checked_in) {
            return response()->json([
                'error_code' => 'TICKET_ALREADY_CHECKED_IN', 
                'message' => 'Vé đã được check-in trước đó',
                'checked_in_at' => $booking->checked_in_at
            ], 422);
        }

        if ($booking->showtime->status !== 'ongoing') {
            return response()->json(['error_code' => 'SHOWTIME_NOT_ONGOING', 'message' => 'Chưa đến giờ chiếu hoặc đã kết thúc'], 422);
        }

        $booking->update([
            'is_checked_in' => true,
            'checked_in_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Check-in successful',
            'booking' => $booking
        ], 200);
    }
}
