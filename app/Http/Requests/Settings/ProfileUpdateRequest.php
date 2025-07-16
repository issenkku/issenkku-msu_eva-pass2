<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'prefix' => 'required|string|max:10',
            'name' => 'required|string|max:100',
            'email' => [
                'required', 'string', 'lowercase', 'email:rfc,dns', 'max:50',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'employee_id' => ['required', 'max:20',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['required', 'string', 'max:20',
                Rule::unique(User::class)->ignore($this->user()->id)],
            'personnel_type' => ['required', 'string'],
            'position_id' => ['required', 'exists:positions,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'bio' => 'nullable|string|max:1000',
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],

            // Password fields
            'current_password' => ['nullable', 'string', 'current_password'],
            'password' => ['nullable', 'confirmed',
            ],
            'password_confirmation' => ['nullable', 'required_with:password'],
        ];
    }
}
