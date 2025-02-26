<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class EditReportRequest extends FormRequest
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
            'data' => __('attributes.report.data'),
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
            'id' => ['required', 'integer', 'exists:reports,id'],
            'data' => ['required', 'json'],
        ];
    }

    public function prepareForValidation()
    {
        return $this->merge([
            'id' => $this->route('report'),
        ]);
    }

}
