<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;

class EditSubjectRequest extends FormRequest
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
            'name' => __('attributes.name'),
            'code' => __('attributes.code'),
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
            'id' => ['required', 'integer', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100'],
        ];
    }

    public function prepareForValidation()
    {
        return $this->merge([
            'id' => $this->route('subject'),
        ]);
    }
}
