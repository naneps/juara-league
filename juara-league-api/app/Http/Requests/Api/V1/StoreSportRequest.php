<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreSportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'min:2', 'max:100', 'unique:sports,name'],
            'type'     => ['required', 'string', 'in:sport,esport'],
            'icon_url' => ['nullable', 'string', 'url'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
