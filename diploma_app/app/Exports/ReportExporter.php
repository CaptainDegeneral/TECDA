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
     * @param array $reportData
     * @param $reportTitle
     * @return BinaryFileResponse
     * @throws \PhpOffice\PhpWord\Exception\Exception
     * @throws Exception
     */
    public static function export(array $reportData, $reportTitle): BinaryFileResponse
    {
        $wordFile = WordReportExporter::export($reportData);

        $excelFile = ExcelReportExporter::export($reportData);

        $zip = new ZipArchive();
        $zipFilename = tempnam(sys_get_temp_dir(), 'ReportArchive') . '.zip';
        if ($zip->open($zipFilename, ZipArchive::CREATE) === TRUE) {
            $wordFilename = WordReportExporter::cleanReportTitle($reportTitle);
            $excelFilename = str_replace('.docx', '.xlsx', $wordFilename);
            $zip->addFile($wordFile, $wordFilename);
            $zip->addFile($excelFile, $excelFilename);
            $zip->close();

            return response()->download($zipFilename, 'Report_' . time() . '.zip')->deleteFileAfterSend();
        }

        throw new Exception('Не удалось создать архив');
    }
}
