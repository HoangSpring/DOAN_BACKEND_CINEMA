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

        return [
            'id' => $this->id,
            'showtime_id' => $this->showtime_id,
            'seat_id' => $this->seat_id,
            'status' => $status,
            'held_by_user_id' => $status === 'holding' ? $this->held_by_user_id : null,
            'hold_expires_at' => $status === 'holding' ? $this->hold_expires_at : null,
            'seat' => new SeatResource($this->whenLoaded('seat')),
        ];
    }
}
