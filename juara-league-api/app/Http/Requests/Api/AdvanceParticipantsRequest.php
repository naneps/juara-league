<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AdvanceParticipantsRequest extends FormRequest
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
            'advancing_participants' => 'required|array|min:1',
            'advancing_participants.*' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'advancing_participants.required' => 'Daftar peserta yang advance wajib diisi.',
            'advancing_participants.array' => 'Advancing participants harus berupa array ID peserta.',
            'advancing_participants.min' => 'Minimal 1 peserta yang advance.',
        ];
    }
}
