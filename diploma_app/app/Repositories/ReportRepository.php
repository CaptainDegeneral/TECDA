<?php

namespace App\Repositories;

use App\Models\Report;
use Illuminate\Pagination\LengthAwarePaginator;
use Log;

class ReportRepository
{
    /**
     * Получение всех отчетов
     * @param string|null $searchValue
     * @return LengthAwarePaginator
     */
    public static function all(?string $searchValue): LengthAwarePaginator
    {
        $reports = Report::with('user:id,last_name,name,surname,email')
            ->orderByDesc('created_at')
            ->select([
                'id',
                'title',
                'name',
                'user_id',
                'created_at',
                'updated_at'
            ]);

        if ($searchValue) {
            $reports->whereLike('title', "%$searchValue%")
                ->orWhereHas('user', function ($query) use ($searchValue) {
                    $query->whereLike('last_name', "%$searchValue%")
                        ->orWhereLike('name', "%$searchValue%")
                        ->orWhereLike('surname', "%$searchValue%");
                });
        }

        return $reports->paginate(10);
    }

    /**
     * Получение отчета по ID
     * @param int $id
     * @return Report
     */
    public static function get(int $id): Report
    {
        $report = Report::with('user:id,last_name,name,surname,email')->select([
            'id',
            'title',
            'name',
            'data',
            'user_id',
            'created_at',
            'updated_at'
        ])->findOrFail($id);

        if ($report && $report->data) {
            if (is_string($report->data)) {
                $decodedData = json_decode($report->data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $report->setAttribute('data', $decodedData);
                } else {
                    $report->setAttribute('data', []);
                }
            }
        }

        return $report;
    }

    /**
     * Получение отчета с декодированными данными
     * @param int $id
     * @return array
     */
    public static function getReportData(int $id): array
    {
        $report = self::get($id);
        return $report->toArray();
    }

    /**
     * Получение отчетов по ID пользователя
     * @param int $userId
     * @param string|null $searchValue
     * @return LengthAwarePaginator
     */
    public static function getByUser(int $userId, ?string $searchValue): LengthAwarePaginator
    {
        $reports = Report::with('user:id,last_name,name,surname,email')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->select(['id', 'title', 'name', 'user_id', 'created_at', 'updated_at']);

        if ($searchValue) {
            $reports->whereLike('title', "%$searchValue%");
        }

        return $reports->paginate(10);
    }

    /**
     * Создание нового отчета
     * @param array $data
     * @return Report
     */
    public static function create(array $data): Report
    {
        return Report::create($data);
    }

    /**
     * Обновление отчета
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update(int $id, array $data): bool
    {
        return Report::where('id', $id)->update($data);
    }

    /**
     * Удаление отчета
     * @param int $id
     * @return bool|null
     */
    public static function delete(int $id): ?bool
    {
        return Report::where('id', $id)->delete();
    }
}
