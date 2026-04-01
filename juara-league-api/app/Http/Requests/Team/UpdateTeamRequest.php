<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('team')->captain_id;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'min:3', 'max:100', 'unique:teams,name,' . $this->route('team')->id],
            'description' => ['nullable', 'string', 'max:500'],
            'logo_url' => ['nullable', 'string', 'url'],
        ];
    }
}
