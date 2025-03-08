<?php

namespace App\Exports;

use Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ReportExporter
{
    /**
     * Экспортирует отчет в Word и Excel, упаковывает в архив.
     *
     * @param array $reportData Данные отчета
     * @param string $reportTitle Заголовок отчета для формирования имени файла
     * @return BinaryFileResponse
     * @throws Exception
     */
    public static function export(array $reportData, string $reportTitle): BinaryFileResponse
    {
        try {
            $wordFile = WordReportExporter::exportDefault($reportData);
            $excelFile = ExcelReportExporter::exportDefault($reportData);

            // Создание экземпляра класса WordReportExporter для более гибкой настройки:
            // $wordExporter = new WordReportExporter([
            //     'styles' => [
            //         'title' => ['bold' => true, 'size' => 16, 'name' => 'Times New Roman']
            //     ]
            // ]);
            // $wordFile = $wordExporter->export($reportData);

            // Создание экземпляра класса ExcelReportExporter для более гибкой настройки:
            // $excelExporter = new ExcelReportExporter([
            //     'chartTypes' => [
            //         'quality' => ['chartTitle' => 'Custom Quality Chart']
            //     ]
            // ]);
            // $excelFile = $excelExporter->export($reportData);

            $zip = new ZipArchive();
            $zipFilename = tempnam(sys_get_temp_dir(), 'ReportArchive') . '.zip';

            if ($zip->open($zipFilename, ZipArchive::CREATE) !== true) {
                throw new Exception('Failed to create ZIP archive');
            }

            $fileName = self::cleanReportTitle($reportTitle);

            $zip->addFile($wordFile, $fileName . '.docx');
            $zip->addFile($excelFile, $fileName . '.xlsx');
            $zip->close();

            return response()->download($zipFilename, 'Report_' . time() . '.zip')->deleteFileAfterSend();
        } catch (Exception $e) {
            throw new Exception('Error during report export: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Очищает заголовок отчета для использования в имени файла
     *
     * @param string $reportTitle Заголовок отчета
     * @return string Очищенный заголовок
     */
    private static function cleanReportTitle(string $reportTitle): string
    {
        $cleanTitle = preg_replace('/[^А-яa-zA-Z0-9\s]+/u', ' ', trim($reportTitle));
        $cleanTitle = preg_replace('/\s+/u', '_', $cleanTitle);

        if (str_contains($cleanTitle, '_от_')) {
            [$title, $date] = explode('_от_', $cleanTitle, 2);
            return $title . '_от_' . str_replace('_', '-', $date);
        }

        return $cleanTitle;
    }
}
