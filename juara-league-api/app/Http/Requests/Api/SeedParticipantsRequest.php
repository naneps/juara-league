<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SeedParticipantsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'seeds' => 'required|array|min:2',
            'seeds.*' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'seeds.required' => 'Daftar seeding wajib diisi.',
            'seeds.array' => 'Seeding harus berupa array ID peserta.',
            'seeds.min' => 'Minimal 2 peserta untuk seeding.',
        ];
    }
}
