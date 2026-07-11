<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMovieRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'genre' => ['nullable', 'string', 'max:100'],
            'age_rating' => ['required', 'in:P,K,T13,T16,T18'],
            'poster_url' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'in:showing,coming_soon,ended'],
            'release_date' => ['nullable', 'date'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }
}
