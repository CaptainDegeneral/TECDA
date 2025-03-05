<?php

namespace App\Http\Requests\Report;

use App\Repositories\ReportRepository;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class ExportReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();

        $report = ReportRepository::get($this->route('report'));

        return $user->role_id === 1 || $report->user_id === $user->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:reports,id'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('report'),
        ]);
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     * @throws HttpResponseException
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            response()->json([
                'data' => [
                    'success' => false,
                    'message' => __('messages.errors.unauthorized'),
                ]
            ], JsonResponse::HTTP_FORBIDDEN)
        );
    }

    public function attributes(): array
    {
        return [
            'id' => __('attributes.id'),
        ];
    }
}
