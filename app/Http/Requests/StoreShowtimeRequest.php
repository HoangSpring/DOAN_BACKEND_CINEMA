<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreShowtimeRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'movie_id' => ['required', 'integer', 'exists:movies,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'start_time' => ['required', 'date', 'after:now'],
            'price_standard' => ['required', 'numeric', 'min:0'],
            'price_vip' => ['required', 'numeric', 'min:0'],
            'price_couple' => ['required', 'numeric', 'min:0'],
        ];
    }
}
