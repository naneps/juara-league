<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTournamentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'sport_id' => ['sometimes', 'uuid', 'exists:sports,id'],
            'description' => ['nullable', 'string'],
            'category' => ['sometimes', 'string'],
            'mode' => ['sometimes', Rule::in(['online', 'offline'])],
            'bracket_type' => ['sometimes', Rule::in(['single', 'double', 'round_robin', 'swiss', 'group_stage'])],
            'participant_type' => ['sometimes', Rule::in(['individual', 'team'])],
            'team_size' => ['nullable', 'integer', 'min:2'],
            'status' => ['sometimes', Rule::in(['draft', 'open', 'ongoing', 'finished', 'canceled'])],
            'venue' => ['nullable', 'string', 'max:255'],
            'banner_url' => ['nullable', 'url'],
            'prize_pool' => ['nullable', 'integer', 'min:0'],
            'entry_fee' => ['nullable', 'integer', 'min:0'],
            'max_participants' => ['sometimes', 'integer', 'min:2'],
            'registration_start_at' => ['nullable', 'date'],
            'registration_end_at' => ['nullable', 'date', 'after:registration_start_at'],
            'start_at' => ['nullable', 'date', 'after:registration_end_at'],
        ];
    }
}
