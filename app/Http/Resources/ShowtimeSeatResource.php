<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ShowtimeSeatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->status;
        if ($status === 'holding' && $this->hold_expires_at && Carbon::now()->greaterThan($this->hold_expires_at)) {
            $status = 'available';
        }

        $seat = $this->whenLoaded('seat') ? $this->seat : null;
        $seatType = $seat?->seat_type ?? 'standard';
        $price = $seatType === 'vip' ? ($this->showtime?->price_vip ?? 0) : ($this->showtime?->price_standard ?? 0);

        return [
            'id' => $this->id,
            'showtime_id' => $this->showtime_id,
            'seat_id' => $this->seat_id,
            'status' => $status,
            'held_by_user_id' => $status === 'holding' ? $this->held_by_user_id : null,
            'hold_expires_at' => $status === 'holding' ? $this->hold_expires_at : null,
            'row' => $seat?->seat_row,
            'seat_row' => $seat?->seat_row,
            'number' => $seat?->seat_number,
            'seat_number' => $seat?->seat_number,
            'type' => $seatType,
            'seat_type' => $seatType,
            'price' => $price,
            'seat' => new SeatResource($seat),
        ];
    }
}
