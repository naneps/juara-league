<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSportRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'min:2', 'max:100', 'unique:sports,name,' . $this->route('sport')],
            'type' => ['sometimes', 'string', 'in:sport,esport'],
            'icon_url' => ['nullable', 'string', 'url'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
