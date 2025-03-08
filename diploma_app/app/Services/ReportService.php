<?php

namespace App\Services;

use App\Http\Requests\Report\EditReportRequest;
use App\Http\Requests\Report\StoreReportRequest;
use App\Models\Report;
use App\Repositories\ReportRepository;
use Exception;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\ReportExporter;

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

            $testReportData = [
                'data' => [
                    'overallResults' => [
                        ['discipline' => 'Mathematics', 'avgQuality' => 82.5, 'avgAverageScore' => 4.2],
                        ['discipline' => 'Physics', 'avgQuality' => 78.3, 'avgAverageScore' => 4.0],
                        ['discipline' => 'Chemistry', 'avgQuality' => 80.1, 'avgAverageScore' => 4.1],
                        ['discipline' => 'Biology', 'avgQuality' => 75.4, 'avgAverageScore' => 3.9],
                        ['discipline' => 'History', 'avgQuality' => 68.2, 'avgAverageScore' => 3.8],
                        ['discipline' => 'Geography', 'avgQuality' => 70.0, 'avgAverageScore' => 3.7],
                        ['discipline' => 'Literature', 'avgQuality' => 85.6, 'avgAverageScore' => 4.3],
                        ['discipline' => 'English', 'avgQuality' => 88.2, 'avgAverageScore' => 4.4],
                        ['discipline' => 'Economics', 'avgQuality' => 77.5, 'avgAverageScore' => 4.0],
                        ['discipline' => 'Computer Science', 'avgQuality' => 92.3, 'avgAverageScore' => 4.5],
                        ['discipline' => 'Philosophy', 'avgQuality' => 65.4, 'avgAverageScore' => 3.5],
                        ['discipline' => 'Art', 'avgQuality' => 90.0, 'avgAverageScore' => 4.6],
                        ['discipline' => 'Music', 'avgQuality' => 87.5, 'avgAverageScore' => 4.4],
                        ['discipline' => 'Physical Education', 'avgQuality' => 74.2, 'avgAverageScore' => 3.8],
                        ['discipline' => 'Sociology', 'avgQuality' => 69.8, 'avgAverageScore' => 3.7],
                        ['discipline' => 'Psychology', 'avgQuality' => 80.0, 'avgAverageScore' => 4.0],
                        ['discipline' => 'Political Science', 'avgQuality' => 73.6, 'avgAverageScore' => 3.9],
                        ['discipline' => 'Law', 'avgQuality' => 91.0, 'avgAverageScore' => 4.7],
                        ['discipline' => 'Business', 'avgQuality' => 84.5, 'avgAverageScore' => 4.3],
                        ['discipline' => 'Statistics', 'avgQuality' => 76.2, 'avgAverageScore' => 4.0],
                        ['discipline' => 'Environmental Science', 'avgQuality' => 79.8, 'avgAverageScore' => 4.1],
                        ['discipline' => 'Geology', 'avgQuality' => 67.4, 'avgAverageScore' => 3.6],
                        ['discipline' => 'Astronomy', 'avgQuality' => 88.9, 'avgAverageScore' => 4.5],
                        ['discipline' => 'Engineering', 'avgQuality' => 93.2, 'avgAverageScore' => 4.8],
                        ['discipline' => 'Medicine', 'avgQuality' => 95.0, 'avgAverageScore' => 4.9],
                        ['discipline' => 'Nursing', 'avgQuality' => 84.0, 'avgAverageScore' => 4.2],
                        ['discipline' => 'Architecture', 'avgQuality' => 82.0, 'avgAverageScore' => 4.1],
                        ['discipline' => 'Linguistics', 'avgQuality' => 77.7, 'avgAverageScore' => 4.0],
                        ['discipline' => 'Anthropology', 'avgQuality' => 70.5, 'avgAverageScore' => 3.8],
                        ['discipline' => 'Drama', 'avgQuality' => 86.3, 'avgAverageScore' => 4.4],
                    ],
                    'finalResults' => [
                        'qualityTable' => [
                            ['discipline' => 'Mathematics', '2018-2019' => 80.5, '2019-2020' => 82.3, '2020-2021' => 81.0, '2021-2022' => null, '2022-2023' => 84.5],
                            ['discipline' => 'Physics', '2018-2019' => 78.2, '2019-2020' => 77.5, '2020-2021' => 79.0, '2021-2022' => 80.1, '2022-2023' => 78.3],
                            ['discipline' => 'Chemistry', '2018-2019' => 80.1, '2019-2020' => null, '2020-2021' => 82.0, '2021-2022' => 81.5, '2022-2023' => 83.0],
                            ['discipline' => 'Biology', '2018-2019' => 75.4, '2019-2020' => 74.8, '2020-2021' => 76.2, '2021-2022' => 75.0, '2022-2023' => null],
                            ['discipline' => 'History', '2018-2019' => 68.2, '2019-2020' => 69.0, '2020-2021' => 70.5, '2021-2022' => 68.0, '2022-2023' => 67.8],
                            ['discipline' => 'Geography', '2018-2019' => 70.0, '2019-2020' => 71.5, '2020-2021' => 70.8, '2021-2022' => 72.0, '2022-2023' => 71.0],
                            ['discipline' => 'Literature', '2018-2019' => 85.6, '2019-2020' => 86.2, '2020-2021' => 85.0, '2021-2022' => null, '2022-2023' => 87.0],
                            ['discipline' => 'English', '2018-2019' => 88.2, '2019-2020' => 89.0, '2020-2021' => 88.5, '2021-2022' => 90.0, '2022-2023' => 89.5],
                            ['discipline' => 'Economics', '2018-2019' => 77.5, '2019-2020' => 78.0, '2020-2021' => 77.0, '2021-2022' => 76.5, '2022-2023' => 77.8],
                            ['discipline' => 'Computer Science', '2018-2019' => 92.3, '2019-2020' => 91.5, '2020-2021' => null, '2021-2022' => 93.0, '2022-2023' => 92.0],
                            ['discipline' => 'Philosophy', '2018-2019' => 65.4, '2019-2020' => 66.0, '2020-2021' => 65.0, '2021-2022' => 64.5, '2022-2023' => 65.8],
                            ['discipline' => 'Art', '2018-2019' => 90.0, '2019-2020' => null, '2020-2021' => 91.2, '2021-2022' => 90.5, '2022-2023' => 89.8],
                            ['discipline' => 'Music', '2018-2019' => 87.5, '2019-2020' => 87.0, '2020-2021' => 88.0, '2021-2022' => 87.8, '2022-2023' => 88.2],
                            ['discipline' => 'Physical Education', '2018-2019' => 74.2, '2019-2020' => 75.0, '2020-2021' => 74.5, '2021-2022' => null, '2022-2023' => 74.8],
                            ['discipline' => 'Sociology', '2018-2019' => 69.8, '2019-2020' => 70.2, '2020-2021' => 69.5, '2021-2022' => 70.0, '2022-2023' => 69.7],
                            ['discipline' => 'Psychology', '2018-2019' => 80.0, '2019-2020' => 80.5, '2020-2021' => 79.8, '2021-2022' => 80.2, '2022-2023' => null],
                            ['discipline' => 'Political Science', '2018-2019' => 73.6, '2019-2020' => 73.0, '2020-2021' => 74.0, '2021-2022' => 73.8, '2022-2023' => 73.5],
                            ['discipline' => 'Law', '2018-2019' => 91.0, '2019-2020' => 90.5, '2020-2021' => 91.2, '2021-2022' => null, '2022-2023' => 91.5],
                            ['discipline' => 'Business', '2018-2019' => 84.5, '2019-2020' => 84.0, '2020-2021' => 85.0, '2021-2022' => 84.8, '2022-2023' => 85.2],
                            ['discipline' => 'Statistics', '2018-2019' => 76.2, '2019-2020' => 77.0, '2020-2021' => null, '2021-2022' => 76.5, '2022-2023' => 76.8],
                            ['discipline' => 'Environmental Science', '2018-2019' => 79.8, '2019-2020' => 80.2, '2020-2021' => 80.0, '2021-2022' => 79.5, '2022-2023' => 80.1],
                            ['discipline' => 'Geology', '2018-2019' => 67.4, '2019-2020' => 68.0, '2020-2021' => 67.8, '2021-2022' => null, '2022-2023' => 68.2],
                            ['discipline' => 'Astronomy', '2018-2019' => 88.9, '2019-2020' => 89.2, '2020-2021' => 88.5, '2021-2022' => 89.0, '2022-2023' => null],
                            ['discipline' => 'Engineering', '2018-2019' => 93.2, '2019-2020' => 93.0, '2020-2021' => 92.8, '2021-2022' => 93.5, '2022-2023' => 93.1],
                            ['discipline' => 'Medicine', '2018-2019' => 95.0, '2019-2020' => null, '2020-2021' => 94.5, '2021-2022' => 95.2, '2022-2023' => 95.0],
                            ['discipline' => 'Nursing', '2018-2019' => 84.0, '2019-2020' => 83.5, '2020-2021' => 84.2, '2021-2022' => 84.0, '2022-2023' => null],
                            ['discipline' => 'Architecture', '2018-2019' => 82.0, '2019-2020' => 82.5, '2020-2021' => 82.2, '2021-2022' => 82.8, '2022-2023' => 82.4],
                            ['discipline' => 'Linguistics', '2018-2019' => 77.7, '2019-2020' => 77.0, '2020-2021' => 77.5, '2021-2022' => null, '2022-2023' => 77.8],
                            ['discipline' => 'Anthropology', '2018-2019' => 70.5, '2019-2020' => 70.2, '2020-2021' => 70.8, '2021-2022' => 70.0, '2022-2023' => 70.3],
                            ['discipline' => 'Drama', '2018-2019' => 86.3, '2019-2020' => 85.8, '2020-2021' => null, '2021-2022' => 86.5, '2022-2023' => 86.0], // Исправлено с 4.4 на корректное значение качества
                        ],
                        'averageScoreTable' => [
                            ['discipline' => 'Mathematics', '2018-2019' => 4.1, '2019-2020' => 4.2, '2020-2021' => 4.1, '2021-2022' => null, '2022-2023' => 4.3],
                            ['discipline' => 'Physics', '2018-2019' => 3.9, '2019-2020' => 4.0, '2020-2021' => 4.0, '2021-2022' => 4.1, '2022-2023' => 3.9],
                            ['discipline' => 'Chemistry', '2018-2019' => 4.0, '2019-2020' => null, '2020-2021' => 4.1, '2021-2022' => 4.2, '2022-2023' => 4.1],
                            ['discipline' => 'Biology', '2018-2019' => 3.8, '2019-2020' => 3.9, '2020-2021' => 3.9, '2021-2022' => 3.8, '2022-2023' => null],
                            ['discipline' => 'History', '2018-2019' => 3.7, '2019-2020' => 3.8, '2020-2021' => 3.8, '2021-2022' => 3.7, '2022-2023' => 3.6],
                            ['discipline' => 'Geography', '2018-2019' => 3.6, '2019-2020' => 3.7, '2020-2021' => 3.7, '2021-2022' => 3.8, '2022-2023' => 3.7],
                            ['discipline' => 'Literature', '2018-2019' => 4.2, '2019-2020' => 4.3, '2020-2021' => 4.2, '2021-2022' => null, '2022-2023' => 4.4],
                            ['discipline' => 'English', '2018-2019' => 4.3, '2019-2020' => 4.4, '2020-2021' => 4.4, '2021-2022' => 4.5, '2022-2023' => 4.4],
                            ['discipline' => 'Economics', '2018-2019' => 3.9, '2019-2020' => 4.0, '2020-2021' => 4.0, '2021-2022' => 3.9, '2022-2023' => 4.1],
                            ['discipline' => 'Computer Science', '2018-2019' => 4.4, '2019-2020' => 4.5, '2020-2021' => null, '2021-2022' => 4.6, '2022-2023' => 4.5],
                            ['discipline' => 'Philosophy', '2018-2019' => 3.4, '2019-2020' => 3.5, '2020-2021' => 3.5, '2021-2022' => 3.4, '2022-2023' => 3.6],
                            ['discipline' => 'Art', '2018-2019' => 4.5, '2019-2020' => null, '2020-2021' => 4.6, '2021-2022' => 4.6, '2022-2023' => 4.5],
                            ['discipline' => 'Music', '2018-2019' => 4.3, '2019-2020' => 4.4, '2020-2021' => 4.4, '2021-2022' => 4.3, '2022-2023' => 4.5],
                            ['discipline' => 'Physical Education', '2018-2019' => 3.7, '2019-2020' => 3.8, '2020-2021' => 3.8, '2021-2022' => null, '2022-2023' => 3.8],
                            ['discipline' => 'Sociology', '2018-2019' => 3.6, '2019-2020' => 3.7, '2020-2021' => 3.7, '2021-2022' => 3.6, '2022-2023' => 3.8],
                            ['discipline' => 'Psychology', '2018-2019' => 3.9, '2019-2020' => 4.0, '2020-2021' => 4.0, '2021-2022' => 4.1, '2022-2023' => null],
                            ['discipline' => 'Political Science', '2018-2019' => 3.8, '2019-2020' => 3.9, '2020-2021' => 3.9, '2021-2022' => 3.8, '2022-2023' => 3.9],
                            ['discipline' => 'Law', '2018-2019' => 4.6, '2019-2020' => 4.7, '2020-2021' => 4.7, '2021-2022' => null, '2022-2023' => 4.8],
                            ['discipline' => 'Business', '2018-2019' => 4.2, '2019-2020' => 4.3, '2020-2021' => 4.3, '2021-2022' => 4.2, '2022-2023' => 4.4],
                            ['discipline' => 'Statistics', '2018-2019' => 3.9, '2019-2020' => 4.0, '2020-2021' => null, '2021-2022' => 4.0, '2022-2023' => 4.1],
                            ['discipline' => 'Environmental Science', '2018-2019' => 4.0, '2019-2020' => 4.1, '2020-2021' => 4.1, '2021-2022' => 4.0, '2022-2023' => 4.2],
                            ['discipline' => 'Geology', '2018-2019' => 3.5, '2019-2020' => 3.6, '2020-2021' => 3.6, '2021-2022' => null, '2022-2023' => 3.7],
                            ['discipline' => 'Astronomy', '2018-2019' => 4.4, '2019-2020' => 4.5, '2020-2021' => 4.5, '2021-2022' => 4.4, '2022-2023' => null],
                            ['discipline' => 'Engineering', '2018-2019' => 4.7, '2019-2020' => 4.8, '2020-2021' => 4.8, '2021-2022' => 4.7, '2022-2023' => 4.9],
                            ['discipline' => 'Medicine', '2018-2019' => 4.8, '2019-2020' => null, '2020-2021' => 4.9, '2021-2022' => 4.9, '2022-2023' => 4.8],
                            ['discipline' => 'Nursing', '2018-2019' => 4.1, '2019-2020' => 4.2, '2020-2021' => 4.2, '2021-2022' => 4.1, '2022-2023' => null],
                            ['discipline' => 'Architecture', '2018-2019' => 4.0, '2019-2020' => 4.1, '2020-2021' => 4.1, '2021-2022' => 4.0, '2022-2023' => 4.2],
                            ['discipline' => 'Linguistics', '2018-2019' => 3.9, '2019-2020' => 4.0, '2020-2021' => 4.0, '2021-2022' => null, '2022-2023' => 4.1],
                            ['discipline' => 'Anthropology', '2018-2019' => 3.7, '2019-2020' => 3.8, '2020-2021' => 3.8, '2021-2022' => 3.7, '2022-2023' => 3.9],
                            ['discipline' => 'Drama', '2018-2019' => 4.3, '2019-2020' => 4.4, '2020-2021' => null, '2021-2022' => 4.4, '2022-2023' => 4.3],
                        ],
                    ],
                ],
                'user' => [
                    'last_name' => 'Иванов',
                    'name' => 'Иван',
                    'surname' => 'Иванович',
                ],
            ];
            $testReportTitle = 'Test Report from 08.03.2025 15:00';


            //return ReportExporter::export($report, $report['title']);
            return ReportExporter::export($testReportData, $testReportTitle);
        } catch (Exception $e) {
            throw new Exception('Export failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
