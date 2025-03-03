<?php

namespace App\Services;

use App\Http\Requests\Report\EditReportRequest;
use App\Http\Requests\Report\StoreReportRequest;
use App\Models\Report;
use App\Repositories\ReportRepository;
use Illuminate\Support\Carbon;

class ReportService
{
    /**
     * @param StoreReportRequest $request
     * @return Report
     */
    public static function create(StoreReportRequest $request): Report
    {
        $formattedDate = Carbon::parse(now())->format('d.m.Y H:i');

        return ReportRepository::create([
            'title' => "Отчет \"$request->name\" от $formattedDate",
            'name' => $request->name,
            'data' => json_encode(
                $request->data,
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION,
            ),
            'user_id' => $request->user_id ?? auth()->id(),
        ]);
    }

    /**
     * @param EditReportRequest $request
     * @return bool
     */
    public static function update(EditReportRequest $request): bool
    {
        return ReportRepository::update($request->id, ['data' => $request->data]);
    }

    /**
     * @param int $id
     * @return bool|null
     */
    public static function delete(int $id): ?bool
    {
        return ReportRepository::delete($id);
    }
}
