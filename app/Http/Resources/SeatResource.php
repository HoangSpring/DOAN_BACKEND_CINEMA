<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_id' => $this->room_id,
            'row' => $this->seat_row,
            'seat_row' => $this->seat_row,
            'row_name' => $this->seat_row,
            'number' => $this->seat_number,
            'seat_number' => $this->seat_number,
            'type' => $this->seat_type,
            'seat_type' => $this->seat_type,
        ];
    }
}
