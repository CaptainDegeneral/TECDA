<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Exception\Exception as PhpWordException;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class WordReportExporter
{
    private const string DEFAULT_VALUE = '-';
    private const array TITLE_STYLE = ['bold' => true, 'size' => 14, 'name' => 'Times New Roman'];
    private const array SUBTITLE_STYLE = ['size' => 12, 'name' => 'Times New Roman'];
    private const array TABLE_STYLE = [
        'borderSize'   => 6,
        'borderColor'  => '000000',
        'cellMargin'   => 80,
        'width'        => 9800,
        'unit'         => 'dxa',
    ];
    private const array HEADER_STYLE = [
        'bold'   => true,
        'valign' => 'center',
        'size'   => 11,
    ];
    private const array CELL_STYLE = [
        'valign' => 'center',
        'size'   => 11,
    ];
    private const array TABLE_CONFIG = [
        'disciplineWidth' => 4000,
    ];

    private const string TITLE_TEXT = 'Результаты промежуточной аттестации';
    private const string TEACHER_PREFIX = 'Преподаватель ';
    private const string DESCRIPTION_TEXT = 'Результаты освоения обучающимися образовательных программ по итогам мониторингов, проводимых организацией (качество знаний с учетом статуса образовательной организации)';
    private const string AVERAGE_SCORE_TEXT = 'По результатам промежуточной аттестации за межаттестационный период средний балл обучающихся составил:';
    private const string QUALITY_TEXT = 'По результатам промежуточной аттестации за межаттестационный период качество знаний обучающихся составило:';

    /**
     * Экспортирует данные отчета в Word-документ.
     *
     * @param array $reportData
     * @return string Путь к временному файлу
     * @throws PhpWordException
     */
    public static function export(array $reportData): string
    {
        self::validateReportData($reportData);

        $finalResults = $reportData['data']['finalResults'];

        $phpWord = new PhpWord();
        $phpWord->setDefaultParagraphStyle(['spaceBefore' => 0, 'spaceAfter' => 0]);
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $phpWord->addNumberingStyle(
            'multilevel',
            [
                'type'   => 'multilevel',
                'levels' => [
                    ['format' => 'decimal', 'text' => '%1.', 'left' => 360, 'hanging' => 360],
                    ['format' => 'decimal', 'text' => '%1.%2.', 'left' => 480, 'hanging' => 480],
                ],
            ]
        );

        $section = $phpWord->addSection([
            'marginLeft' => 1200, 'marginRight' => 900, 'marginTop' => 900, 'marginBottom' => 900,
        ]);

        self::addTitle($section);
        self::addTeacherName($section, $reportData);
        self::addEmptyLine($section, 1.15);
        self::addDescription($section);
        self::addEmptyLine($section, 1.15);
        self::addAverageScoreText($section);
        self::addEmptyLine($section, 1.0);
        self::addGenericTable($section, $finalResults['averageScoreTable'], 'Средний бал');
        self::addEmptyLine($section, 1.15);
        self::addQualityText($section);
        self::addEmptyLine($section, 1.0);
        self::addGenericTable($section, $finalResults['qualityTable'], 'Уровень качества знаний, %');

        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $writer = IOFactory::createWriter($phpWord);
        $writer->save($tempFile);

        return $tempFile;
    }

    // Перенос всех остальных методов из исходного ReportExporter.php
    private static function formatTeacherName(array $reportData): string
    {
        $user = $reportData['user'] ?? null;
        if ($user && isset($user['last_name'], $user['name'], $user['surname'])) {
            $initials = mb_substr($user['name'], 0, 1) . '.' . mb_substr($user['surname'], 0, 1) . '.';
            return $user['last_name'] . ' ' . $initials;
        }
        return 'Шинакова С.В.';
    }

    private static function addGenericTable(Section $section, array $data, string $tableHeaderText): void
    {
        $table = $section->addTable(self::TABLE_STYLE);

        $allKeys = array_keys($data[0]);
        $disciplineKey = 'discipline';
        $yearKeys = array_filter($allKeys, fn($key) => $key !== $disciplineKey);
        $yearCount = count($yearKeys);

        $headerRow = $table->addRow();
        $headerRow->addCell(self::TABLE_CONFIG['disciplineWidth'], array_merge(self::HEADER_STYLE, ['vMerge' => 'restart']))->addText('Дисциплина', self::HEADER_STYLE);
        $headerCell = $headerRow->addCell(self::TABLE_STYLE['width'] - self::TABLE_CONFIG['disciplineWidth'], array_merge(self::HEADER_STYLE, ['gridSpan' => $yearCount, 'vMerge' => 'continue']));
        $headerCell->addText($tableHeaderText, self::HEADER_STYLE);

        $subHeaderRow = $table->addRow();
        $subHeaderRow->addCell(null, ['vMerge' => 'continue']);
        $valueWidth = (self::TABLE_STYLE['width'] - self::TABLE_CONFIG['disciplineWidth']) / $yearCount;
        foreach ($yearKeys as $year) {
            $subHeaderRow->addCell($valueWidth, self::HEADER_STYLE)->addText($year, self::HEADER_STYLE);
        }

        foreach ($data as $row) {
            $dataRow = $table->addRow();
            $dataRow->addCell(self::TABLE_CONFIG['disciplineWidth'], self::CELL_STYLE)->addText($row[$disciplineKey] ?? self::DEFAULT_VALUE, self::CELL_STYLE);
            foreach ($yearKeys as $year) {
                $value = $row[$year] ?? self::DEFAULT_VALUE;
                $dataRow->addCell($valueWidth, self::CELL_STYLE)->addText($value, self::CELL_STYLE);
            }
        }
    }

    public static function cleanReportTitle(string $reportTitle): string
    {
        $cleanTitle = preg_replace('/[^А-яa-zA-Z0-9]/u', ' ', $reportTitle);
        $cleanTitle = preg_replace('/\s+/', ' ', trim($cleanTitle));
        $cleanTitle = str_replace(' ', '_', $cleanTitle);

        $parts = explode('_от_', $cleanTitle);
        if (count($parts) === 2) {
            $dateTimePart = str_replace('_', '-', $parts[1]);
            return $parts[0] . '_от_' . $dateTimePart . '.docx';
        }
        return $cleanTitle . '.docx';
    }

    private static function validateReportData(array $data): void
    {
        Log::info('WordReportExporter::validateReportData - Validating data:', $data);

        if (
            empty($data['data']['finalResults']['averageScoreTable']) ||
            empty($data['data']['finalResults']['qualityTable']) ||
            !isset($data['user'])
        ) {
            throw new InvalidArgumentException('Invalid report data structure');
        }
    }

    private static function addTitle(Section $section): void
    {
        $section->addText(self::TITLE_TEXT, self::TITLE_STYLE, ['alignment' => 'center']);
    }

    private static function addTeacherName(Section $section, array $reportData): void
    {
        $teacherName = self::formatTeacherName($reportData);
        $section->addText(self::TEACHER_PREFIX . $teacherName, self::SUBTITLE_STYLE, ['alignment' => 'center']);
    }

    private static function addDescription(Section $section): void
    {
        $descriptionStyle = ['size' => 11, 'name' => 'Times New Roman'];
        $paragraphStyle = ['alignment' => Jc::BOTH];
        $section->addListItem(self::DESCRIPTION_TEXT, 1, $descriptionStyle, 'multilevel', $paragraphStyle);
    }

    private static function addAverageScoreText(Section $section): void
    {
        $textStyle = ['size' => 11, 'name' => 'Times New Roman', 'lineHeight' => 1.0, 'spaceBefore' => 0, 'spaceAfter' => 0];
        $paragraphStyle = ['indentation' => ['firstLine' => 480], 'alignment' => Jc::BOTH];
        $section->addText(self::AVERAGE_SCORE_TEXT, $textStyle, $paragraphStyle);
    }

    private static function addQualityText(Section $section): void
    {
        $textStyle = ['size' => 11, 'name' => 'Times New Roman', 'lineHeight' => 1.0, 'spaceBefore' => 0, 'spaceAfter' => 0];
        $paragraphStyle = ['indentation' => ['firstLine' => 480], 'alignment' => Jc::BOTH];
        $section->addText(self::QUALITY_TEXT, $textStyle, $paragraphStyle);
    }

    private static function addEmptyLine(Section $section, float $lineHeight): void
    {
        $section->addText('', ['lineHeight' => $lineHeight]);
    }
}
