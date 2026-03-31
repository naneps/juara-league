<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string', 'min:2', 'max:100', 'unique:sports,name,' . $this->route('id')],
            'type'     => ['sometimes', 'string', 'in:sport,esport'],
            'icon_url' => ['nullable', 'string', 'url'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
