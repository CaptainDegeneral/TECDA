<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class EditUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [
            'id' => __('attributes.id'),
            'last_name' => __('attributes.user.last_name'),
            'name' => __('attributes.user.name'),
            'surname' => __('attributes.user.surname'),
            'email' => __('attributes.user.email'),
            'verified' => __('attributes.user.verified'),
            'password' => __('attributes.user.password'),
            'role_id' => __('attributes.user.role_id'),
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:users,id'],
            'last_name' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'verified' => ['required', 'boolean'],
            'password' => ['nullable', Rules\Password::defaults()],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }

    public function prepareForValidation()
    {
        return $this->merge([
            'id' => $this->route('user'),
        ]);
    }
}
