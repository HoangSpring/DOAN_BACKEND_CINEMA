<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:rooms,name'],
            'room_type' => ['nullable', 'in:2D,3D,IMAX'],
            'rows' => ['required', 'integer', 'min:1', 'max:50'],
            'seats_per_row' => ['required', 'integer', 'min:1', 'max:100'],
            'vip_rows' => ['nullable', 'array'],
            'vip_rows.*' => ['string', 'max:5'],
        ];
    }
}
