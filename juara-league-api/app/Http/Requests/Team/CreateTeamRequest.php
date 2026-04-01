<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class CreateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100', 'unique:teams,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'logo_url' => ['nullable', 'string', 'url'],
        ];
    }
}
