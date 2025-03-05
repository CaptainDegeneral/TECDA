<?php

namespace App\Exports;

use InvalidArgumentException;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExporter
{
    private const string DEFAULT_VALUE = '-';
    private const array TITLE_STYLE = ['bold' => true, 'size' => 14];
    private const array TABLE_STYLE = [
        'borderSize' => 6,
        'borderColor' => '000000',
        'cellMargin' => 80
    ];
    private const array HEADER_STYLE = [
        'bold' => true,
        'bgColor' => 'D3D3D3'
    ];
    private const array CELL_STYLE = [
        'valign' => 'center'
    ];

    private const array TABLE_CONFIG = [
        'disciplineWidth' => 4000,
        'valueWidth' => 2000,
        'titleSize' => 14,
    ];

    /**
     * Экспортирует данные отчета в Word-документ
     *
     * @param array $reportData
     * @param int $reportId
     * @param string $reportTitle
     * @return BinaryFileResponse
     * @throws Exception
     */
    public static function export(array $reportData, int $reportId, string $reportTitle): BinaryFileResponse
    {
        self::validateReportData($reportData);

        $finalResults = $reportData['finalResults'];
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        self::addTableToSection($section, 'Таблица успеваемости', $finalResults['performanceTable']);
        $section->addTextBreak(2);
        self::addTableToSection($section, 'Таблица качества образования', $finalResults['qualityTable']);

        return self::generateWordResponse($phpWord, $reportTitle);
    }

    /**
     * Добавляет таблицу в секцию документа
     *
     * @param Section $section
     * @param string $title
     * @param array $data
     * @return void
     */
    private static function addTableToSection(Section $section, string $title, array $data): void
    {
        $section->addText($title, self::TITLE_STYLE, ['alignment' => 'center']);
        $section->addTextBreak(1);

        $table = $section->addTable(self::TABLE_STYLE);
        $years = array_keys($data[0]);
        unset($years[array_search('discipline', $years)]);

        self::addTableHeader($table, $years);
        foreach ($data as $row) {
            self::addTableRow($table, $row, $years);
        }
    }

    /**
     * Добавляет заголовок таблицы
     *
     * @param Table $table
     * @param array $years
     * @return void
     */
    private static function addTableHeader(Table $table, array $years): void
    {
        $row = $table->addRow();
        $row->addCell(self::TABLE_CONFIG['disciplineWidth'], self::HEADER_STYLE)
            ->addText('Дисциплина', ['bold' => true]);

        foreach ($years as $year) {
            $row->addCell(self::TABLE_CONFIG['valueWidth'], self::HEADER_STYLE)
                ->addText($year, ['bold' => true]);
        }
    }

    /**
     * Добавляет строку данных в таблицу
     *
     * @param Table $table
     * @param array $row
     * @param array $years
     * @return void
     */
    private static function addTableRow(Table $table, array $row, array $years): void
    {
        $tableRow = $table->addRow();
        $tableRow->addCell(self::TABLE_CONFIG['disciplineWidth'], self::CELL_STYLE)
            ->addText($row['discipline']);

        foreach ($years as $year) {
            $value = $row[$year] ?? self::DEFAULT_VALUE;
            $tableRow->addCell(self::TABLE_CONFIG['valueWidth'], self::CELL_STYLE)
                ->addText($value);
        }
    }

    /**
     * Генерирует и возвращает Word-файл
     *
     * @param PhpWord $phpWord
     * @param string $reportTitle
     * @return BinaryFileResponse
     * @throws Exception
     */
    private static function generateWordResponse(PhpWord $phpWord, string $reportTitle): BinaryFileResponse
    {
        $cleanTitle = preg_replace('/[^А-яa-zA-Z0-9]/u', ' ', $reportTitle);
        $cleanTitle = preg_replace('/\s+/', ' ', trim($cleanTitle));
        $cleanTitle = str_replace(' ', '_', $cleanTitle);

        $parts = explode('_от_', $cleanTitle);
        if (count($parts) === 2) {
            $dateTimePart = $parts[1];
            $dateTimePart = str_replace('_', '-', $dateTimePart);
            $filename = $parts[0] . '_от_' . $dateTimePart . '.docx';
        } else {
            $filename = $cleanTitle . '.docx';
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename),
        ])->deleteFileAfterSend(true);
    }

    /**
     * Валидирует данные отчета
     *
     * @param array $data
     * @throws InvalidArgumentException
     * @return void
     */
    private static function validateReportData(array $data): void
    {
        if (empty($data['finalResults']['performanceTable']) ||
            empty($data['finalResults']['qualityTable'])) {
            throw new InvalidArgumentException('Invalid report data structure');
        }
    }
}
