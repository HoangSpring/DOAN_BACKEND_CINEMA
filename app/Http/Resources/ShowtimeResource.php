<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowtimeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movie_id' => $this->movie_id,
            'room_id' => $this->room_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'price_standard' => $this->price_standard,
            'price_vip' => $this->price_vip,
            'status' => $this->status,
        ];
    }
}
