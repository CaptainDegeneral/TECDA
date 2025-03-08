<?php

namespace App\Services;

use App\Exports\ReportExporter;
use App\Http\Requests\Report\EditReportRequest;
use App\Http\Requests\Report\StoreReportRequest;
use App\Models\Report;
use App\Repositories\ReportRepository;
use Database\Factories\ReportDataFactory;
use Exception;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    /**
     * Экспортирует отчет в Word и Excel, возвращает архив.
     *
     * @param int $reportId
     * @return BinaryFileResponse
     * @throws Exception
     */
    public static function export(int $reportId): BinaryFileResponse
    {
        $report = ReportRepository::getReportData($reportId);

        if (isset($report['data']) && is_string($report['data'])) {
            $decodedData = json_decode($report['data'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data in report');
            }
            $report['data'] = $decodedData;
        }

        try {
            return ReportExporter::export($report, $report['title']);
        } catch (Exception $e) {
            throw new Exception('Export failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Экспортирует тестовый отчет с предопределёнными данными.
     *
     * @return BinaryFileResponse
     * @throws Exception
     */
    public static function exportTest(): BinaryFileResponse
    {
        $testData = ReportDataFactory::getTestReportData(10, 3);;
        $testTitle = ReportDataFactory::getTestReportTitle();

        try {
            return ReportExporter::export($testData, $testTitle);
        } catch (Exception $e) {
            throw new Exception('Test export failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
