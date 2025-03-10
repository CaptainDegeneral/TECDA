<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $id
 */
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
            'name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('subjects', 'name')->ignore($this->id),
                'required_without:code',
            ],
            'code' => [
                'nullable',
                'string',
                'max:100',
                'required_without:name',
            ],
        ];
    }

    public function prepareForValidation()
    {
        return $this->merge([
            'id' => $this->route('subject'),
        ]);
    }
}
