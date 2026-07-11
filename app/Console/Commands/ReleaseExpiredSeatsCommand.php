<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShowtimeSeat;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReleaseExpiredSeatsCommand extends Command
{
    protected $signature = 'app:release-expired-seats';
    protected $description = 'Release seats that have been held for too long without payment';

    public function handle()
    {
        $now = Carbon::now();

        try {
            DB::beginTransaction();

            $releasedCount = ShowtimeSeat::where('status', 'holding')
                ->where('hold_expires_at', '<', $now)
                ->update([
                    'status' => 'available',
                    'held_by_user_id' => null
                ]);

            $expiredBookings = Booking::where('status', 'pending')
                ->where('created_at', '<', $now->copy()->subMinutes(5))
                ->update([
                    'status' => 'expired'
                ]);

            DB::commit();

            $this->info("Released {$releasedCount} seats and expired {$expiredBookings} bookings.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to release seats: " . $e->getMessage());
        }
    }
}
