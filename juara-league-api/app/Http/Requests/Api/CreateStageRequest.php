<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateStageRequest extends FormRequest
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
            'name' => 'required|string|min:2|max:100',
            'type' => 'required|string|in:single_elim,double_elim,round_robin,swiss',
            'bo_format' => 'required|string|in:bo1,bo3,bo5,bo7',
            'participants_advance' => 'required|integer|min:1',
            'groups_count' => 'required_if:type,round_robin|nullable|integer|min:1',
            'participants_per_group' => 'required_if:type,round_robin|nullable|integer|min:2',
            'settings' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama stage wajib diisi.',
            'name.min' => 'Nama stage minimal 2 karakter.',
            'name.max' => 'Nama stage maksimal 100 karakter.',
            'type.required' => 'Format stage wajib dipilih.',
            'type.in' => 'Format stage tidak valid. Pilihan: single_elim, double_elim, round_robin, swiss.',
            'bo_format.required' => 'Format BO wajib dipilih.',
            'bo_format.in' => 'Format BO tidak valid. Pilihan: bo1, bo3, bo5, bo7.',
            'participants_advance.required' => 'Jumlah peserta yang advance wajib diisi.',
            'participants_advance.min' => 'Minimal 1 peserta yang advance.',
            'groups_count.required_if' => 'Jumlah grup wajib diisi untuk format Round Robin.',
            'participants_per_group.required_if' => 'Jumlah peserta per grup wajib diisi untuk format Round Robin.',
            'participants_per_group.min' => 'Minimal 2 peserta per grup.',
        ];
    }
}
