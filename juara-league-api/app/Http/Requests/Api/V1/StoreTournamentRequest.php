<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTournamentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We handles authorization in the controller/policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'sport_id' => ['required', 'uuid', 'exists:sports,id'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string'],
            'mode' => ['required', Rule::in(['online', 'offline'])],
            'bracket_type' => ['required', Rule::in(['single', 'double', 'round_robin', 'swiss', 'group_stage'])],
            'venue' => ['nullable', 'string', 'max:255', 'required_if:mode,offline'],
            'banner_url' => ['nullable', 'url'],
            'prize_pool' => ['nullable', 'integer', 'min:0'],
            'entry_fee' => ['nullable', 'integer', 'min:0'],
            'max_participants' => ['required', 'integer', 'min:2'],
            'registration_start_at' => ['nullable', 'date'],
            'registration_end_at' => ['nullable', 'date', 'after:registration_start_at'],
            'start_at' => ['nullable', 'date', 'after:registration_end_at'],
        ];
    }
}
