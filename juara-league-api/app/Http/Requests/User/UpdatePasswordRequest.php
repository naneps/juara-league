<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow if the user is authenticated via email/password, not solely Google OAuth.
        // We can check if they have a password set to allow password updating.
        return $this->user() && $this->user()->password !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Za-z]/', 'regex:/[0-9]/', 'different:current_password'],
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'new_password.regex' => 'The new password must contain at least one letter and one number.',
            'new_password.different' => 'The new password must be different from the current password.',
        ];
    }
}
