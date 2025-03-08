<?php

namespace App\Abstracts;

use InvalidArgumentException;

abstract class ReportDataValidator
{
    /**
     * @var array Данные отчета
     */
    protected array $reportData;

    private const string ERROR_MISSING_DATA = "Отсутствует обязательный ключ 'data' в данных отчёта";
    private const string ERROR_MISSING_FINAL_RESULTS = "Отсутствует обязательный ключ 'finalResults' в 'data'";
    private const string ERROR_MISSING_FINAL_RESULT_KEY = "Отсутствует обязательный ключ '%s' в 'data.finalResults'";
    private const string ERROR_INVALID_FINAL_RESULT_KEY_TYPE = "Ключ '%s' в 'data.finalResults' должен быть массивом";
    private const string ERROR_MISSING_OPTIONAL_KEYS = "Данные отчёта должны содержать хотя бы один из опциональных ключей: 'overallResults' или 'user'";
    private const string ERROR_INVALID_OVERALL_RESULTS_TYPE = "Ключ 'overallResults' в 'data' должен быть массивом";
    private const string ERROR_INVALID_USER_TYPE = "Ключ 'user' должен быть массивом";

    /**
     * Валидирует структуру данных отчета
     *
     * @throws InvalidArgumentException При некорректной структуре данных
     */
    protected function validateReportData(): void
    {
        if (empty($this->reportData['data'])) {
            throw new InvalidArgumentException(self::ERROR_MISSING_DATA);
        }

        if (empty($this->reportData['data']['finalResults'])) {
            throw new InvalidArgumentException(self::ERROR_MISSING_FINAL_RESULTS);
        }

        $requiredFinalResultKeys = ['averageScoreTable', 'qualityTable'];
        foreach ($requiredFinalResultKeys as $key) {
            if (empty($this->reportData['data']['finalResults'][$key])) {
                throw new InvalidArgumentException(sprintf(self::ERROR_MISSING_FINAL_RESULT_KEY, $key));
            }
            if (!is_array($this->reportData['data']['finalResults'][$key])) {
                throw new InvalidArgumentException(sprintf(self::ERROR_INVALID_FINAL_RESULT_KEY_TYPE, $key));
            }
        }

        if (
            empty($this->reportData['data']['overallResults']) &&
            empty($this->reportData['user'])
        ) {
            throw new InvalidArgumentException(self::ERROR_MISSING_OPTIONAL_KEYS);
        }

        if (!empty($this->reportData['data']['overallResults']) && !is_array($this->reportData['data']['overallResults'])) {
            throw new InvalidArgumentException(self::ERROR_INVALID_OVERALL_RESULTS_TYPE);
        }
        if (!empty($this->reportData['user']) && !is_array($this->reportData['user'])) {
            throw new InvalidArgumentException(self::ERROR_INVALID_USER_TYPE);
        }
    }
}
