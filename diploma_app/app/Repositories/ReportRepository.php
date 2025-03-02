<?php

namespace App\Repositories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Collection;

class ReportRepository
{
    /**
     * Получение всех отчетов
     * @return Collection
     */
    public static function all(): Collection
    {
        return Report::with('user:id,last_name,name,surname,email')
            ->orderByDesc('created_at')
            ->get([
            'id',
            'name',
            'user_id',
            'created_at',
            'updated_at'
        ]);
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
            'name',
            'data',
            'user_id',
            'created_at',
            'updated_at'
        ])->find($id);

        if ($report && $report->data) {
            $report->data = json_decode($report->data, true);
        }

        return $report;
    }

    /**
     * Получение отчетов по ID пользователя
     * @param int $userId
     * @return Collection
     */
    public static function getByUser(int $userId): Collection
    {
        return Report::with('user:id,last_name,name,surname,email')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'user_id', 'created_at', 'updated_at']);
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
