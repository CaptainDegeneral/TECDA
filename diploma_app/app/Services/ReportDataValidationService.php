<?php

namespace App\Services;

use InvalidArgumentException;

class ReportDataValidationService
{
    private const string ERROR_MISSING_DATA = "Отсутствует обязательный ключ 'data' в данных отчёта";
    private const string ERROR_MISSING_FINAL_RESULTS = "Отсутствует обязательный ключ 'finalResults' в 'data'";
    private const string ERROR_MISSING_FINAL_RESULT_KEY = "Отсутствует обязательный ключ '%s' в 'data.finalResults'";
    private const string ERROR_INVALID_FINAL_RESULT_KEY_TYPE = "Ключ '%s' в 'data.finalResults' должен быть массивом";
    private const string ERROR_MISSING_OPTIONAL_KEYS = "Данные отчёта должны содержать хотя бы один из опциональных ключей: 'overallResults' или 'user'";
    private const string ERROR_INVALID_OVERALL_RESULTS_TYPE = "Ключ 'overallResults' в 'data' должен быть массивом";
    private const string ERROR_INVALID_USER_TYPE = "Ключ 'user' должен быть массивом";

    public function validate(array $reportData): void
    {
        if (empty($reportData['data'])) {
            throw new InvalidArgumentException(self::ERROR_MISSING_DATA);
        }

        if (empty($reportData['data']['finalResults'])) {
            throw new InvalidArgumentException(self::ERROR_MISSING_FINAL_RESULTS);
        }

        $requiredFinalResultKeys = ['averageScoreTable', 'qualityTable'];
        foreach ($requiredFinalResultKeys as $key) {
            if (empty($reportData['data']['finalResults'][$key])) {
                throw new InvalidArgumentException(sprintf(self::ERROR_MISSING_FINAL_RESULT_KEY, $key));
            }
            if (!is_array($reportData['data']['finalResults'][$key])) {
                throw new InvalidArgumentException(sprintf(self::ERROR_INVALID_FINAL_RESULT_KEY_TYPE, $key));
            }
        }

        if (empty($reportData['data']['overallResults']) && empty($reportData['user'])) {
            throw new InvalidArgumentException(self::ERROR_MISSING_OPTIONAL_KEYS);
        }

        if (!empty($reportData['data']['overallResults']) && !is_array($reportData['data']['overallResults'])) {
            throw new InvalidArgumentException(self::ERROR_INVALID_OVERALL_RESULTS_TYPE);
        }
        if (!empty($reportData['user']) && !is_array($reportData['user'])) {
            throw new InvalidArgumentException(self::ERROR_INVALID_USER_TYPE);
        }
    }
}
