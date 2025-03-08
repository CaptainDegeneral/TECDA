<?php

namespace App\Exports;


use PhpOffice\PhpSpreadsheet\Chart\Axis;
use PhpOffice\PhpSpreadsheet\Chart\AxisText;
use PhpOffice\PhpSpreadsheet\Chart\GridLines;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Percentage;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class ExcelReportExporter
{
    private const string DEFAULT_VALUE = '-';

    /**
     * Экспортирует данные отчета в Excel-документ с тремя листами.
     *
     * @param array $reportData
     * @return string Путь к временному файлу
     */
    public static function export(array $reportData): string
    {
        $spreadsheet = new Spreadsheet();

        self::createQualityResultsSheet($spreadsheet, $reportData);
        self::createAverageScoreResultsSheet($spreadsheet, $reportData);

        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->setActiveSheetIndex(0);

        $tempFile = tempnam(sys_get_temp_dir(), 'PHPSpreadsheet');

        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->save($tempFile);

        return $tempFile;
    }

    /**
     * Создает лист с таблицей данных и диаграммой
     * для общих метрик качества знаний и среднего балла (за все периоды)
     *
     * @param Spreadsheet $spreadsheet
     * @param array $reportData
     * @param string $sheetTitle Название листа
     * @param string $chartTitle Заголовок диаграммы
     * @param string $dataRange Диапазон данных для диаграммы
     * @param string $categoryRange Диапазон категорий для диаграммы
     * @param int $chartPositionX Позиция по X диаграммы
     * @param int $chartPositionY Позиция по Y диаграммы
     */
    private static function createChartResultsSheet(Spreadsheet $spreadsheet, array $reportData, string $sheetTitle, string $chartTitle, string $dataRange, string $categoryRange, int $chartPositionX, int $chartPositionY): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($sheetTitle);

        $sheetData = self::prepareSheetData($reportData['data']['overallResults']);
        self::fillSheetWithData($sheet, $sheetData);

        if (count($sheetData) > 1) {
            // Создание категории и значений для диаграммы
            $categories = new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_STRING,
                "$sheetTitle!$categoryRange" . count($sheetData),
                null,
                count($sheetData) - 1
            );

            $values = new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_NUMBER,
                "$sheetTitle!$dataRange" . count($sheetData),
                null,
                count($sheetData) - 1
            );

            $series = new DataSeries(
                DataSeries::TYPE_BARCHART,
                DataSeries::GROUPING_STANDARD,
                [0],
                [],
                [$categories],
                [$values]
            );

            // Создание оси X с поворотом подписей
            $xAxis = new Axis();
            $xAxisText = new AxisText();
            $xAxisText->setRotation(-45);
            $xAxis->setAxisText($xAxisText);

            // Создание оси Y с сеткой
            $yAxis = new Axis();
            $gridLines = new GridLines();
            $gridLines->setLineColorProperties('d9d9d9');
            $yAxis->setMajorGridlines($gridLines);

            // Создание диаграммы
            $layout = new Layout();
            $layout->setShowVal(true);

            $chart = new Chart(
                'Chart',
                new Title($chartTitle),
                null,
                new PlotArea($layout, [$series]),
                true,
                DataSeries::EMPTY_AS_GAP,
                null,
                null,
                $xAxis,
                $yAxis
            );

            $chart->setTopLeftPosition("E$chartPositionX")->setBottomRightPosition("N$chartPositionY");
            $sheet->addChart($chart);
        }
    }

    /**
     * Создает лист с таблицей данных и диаграммой качества
     *
     * @param Spreadsheet $spreadsheet
     * @param array $reportData
     */
    private static function createQualityResultsSheet(Spreadsheet $spreadsheet, array $reportData): void
    {
        self::createChartResultsSheet(
            $spreadsheet,
            $reportData,
            'QualityResults',
            'Качество знаний',
            '$B$2:$B$',
            '$A$2:$A$',
            2,
            30
        );
    }

    /**
     * Создает лист с таблицей данных и диаграммой среднего балла
     *
     * @param Spreadsheet $spreadsheet
     * @param array $reportData
     */
    private static function createAverageScoreResultsSheet(Spreadsheet $spreadsheet, array $reportData): void
    {
        self::createChartResultsSheet(
            $spreadsheet,
            $reportData,
            'AverageScoreResults',
            'Средний балл',
            '$C$2:$C$',
            '$A$2:$A$',
            2,
            30
        );
    }

    /**
     * Подготавливает данные для таблицы
     *
     * @param array $overallResults
     * @return array
     */
    private static function prepareSheetData(array $overallResults): array
    {
        $sheetData = [['Дисциплина', 'Качество знаний', 'Средний балл']];

        foreach ($overallResults as $row) {
            $quality = $row['avgQuality'] !== null ? $row['avgQuality'] / 100 : self::DEFAULT_VALUE;
            $sheetData[] = [
                $row['discipline'],
                $quality,
                $row['avgAverageScore'] ?? self::DEFAULT_VALUE,
            ];
        }

        return $sheetData;
    }

    /**
     * Заполняет лист данными и применяет форматирование
     *
     * @param Worksheet $sheet
     * @param array $sheetData
     */
    private static function fillSheetWithData(Worksheet $sheet, array $sheetData): void
    {
        $sheet->fromArray($sheetData, null, 'A1');

        foreach (range('A', 'C') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->getStyle('A1:C1')->getFont()->setBold(true)->setSize(11);

        for ($i = 2; $i <= count($sheetData); $i++) {
            $percentageFormat = new Percentage();
            $sheet->getCell('B' . $i)
                ->getStyle()->getNumberFormat()
                ->setFormatCode($percentageFormat);
        }
    }
}
