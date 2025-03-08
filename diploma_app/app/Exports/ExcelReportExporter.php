<?php

namespace App\Exports;

use Exception;
use PhpOffice\PhpSpreadsheet\Chart\Axis;
use PhpOffice\PhpSpreadsheet\Chart\AxisText;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\GridLines;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Percentage;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class ExcelReportExporter
{
    private const string DEFAULT_VALUE = '-';
    private const int MIN_CHART_WIDTH_COLUMNS = 10;
    private const int MIN_CHART_END_ROW = 25;
    private const int CHART_START_ROW = 2;

    private const array CHART_TYPES = [
        'quality' => [
            'sheetTitle' => 'QualityResults',
            'chartTitle' => 'Качество знаний',
            'dataRange' => '$B$2:$B$',
            'categoryRange' => '$A$2:$A$',
        ],
        'averageScore' => [
            'sheetTitle' => 'AverageScoreResults',
            'chartTitle' => 'Средний балл',
            'dataRange' => '$C$2:$C$',
            'categoryRange' => '$A$2:$A$',
        ],
    ];

    private const array PERIODS_CHART_TYPES = [
        'quality' => [
            'sheetTitle' => 'QualityPerPeriods',
            'chartTitle' => 'Качество знаний по годам',
            'isPercentage' => true,
            'tableTitle' => 'Качество знаний',
            'tableKey' => 'qualityTable',
        ],
        'averageScore' => [
            'sheetTitle' => 'AverageScorePerPeriods',
            'chartTitle' => 'Средний балл по годам',
            'isPercentage' => false,
            'tableTitle' => 'Средний балл',
            'tableKey' => 'averageScoreTable',
        ],
    ];

    /**
     * Экспортирует данные отчета в Excel-документ с N листами.
     *
     * @param array $reportData
     * @return string Путь к временному файлу
     * @throws Exception При ошибке создания Excel-файла
     */
    public static function export(array $reportData): string
    {
        try {
            $spreadsheet = new Spreadsheet();

            foreach (self::CHART_TYPES as $type => $config) {
                self::createChartResultsSheet(
                    $spreadsheet,
                    $reportData,
                    $config['sheetTitle'],
                    $config['chartTitle'],
                    $config['dataRange'],
                    $config['categoryRange']
                );
            }

            foreach (self::PERIODS_CHART_TYPES as $type => $config) {
                self::createPeriodsResultsSheet(
                    $spreadsheet,
                    $reportData,
                    $config
                );
            }

            $spreadsheet->removeSheetByIndex(0);
            $spreadsheet->setActiveSheetIndex(0);

            $tempFile = tempnam(sys_get_temp_dir(), 'PHPSpreadsheet');

            $writer = new Xlsx($spreadsheet);
            $writer->setIncludeCharts(true);
            $writer->save($tempFile);

            return $tempFile;
        } catch (Throwable $e) {
            throw new Exception('Failed to create Excel report: ' . $e->getMessage(), 0, $e);
        }
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
     */
    private static function createChartResultsSheet(
        Spreadsheet $spreadsheet,
        array $reportData,
        string $sheetTitle,
        string $chartTitle,
        string $dataRange,
        string $categoryRange
    ): void {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($sheetTitle);

        $sheetData = self::prepareSheetData($reportData['data']['overallResults']);
        self::fillSheetWithData($sheet, $sheetData);

        if (count($sheetData) <= 1) {
            return;
        }

        $rowCount = count($sheetData);
        $disciplineCount = $rowCount - 1;
        $tableColumnsCount = count($sheetData[0]);

        $chartPosition = self::calculateChartPosition($disciplineCount, $rowCount, $tableColumnsCount);

        $chart = self::createBarChart(
            $sheetTitle,
            $chartTitle,
            $categoryRange,
            $dataRange,
            $rowCount,
            $disciplineCount
        );

        $chart->setTopLeftPosition($chartPosition['topLeft'])
            ->setBottomRightPosition($chartPosition['bottomRight']);

        $sheet->addChart($chart);
    }

    /**
     * Создает лист с таблицей данных и диаграммой по периодам
     *
     * @param Spreadsheet $spreadsheet
     * @param array $reportData
     * @param array $config Конфигурация листа и диаграммы
     */
    private static function createPeriodsResultsSheet(
        Spreadsheet $spreadsheet,
        array $reportData,
        array $config
    ): void {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($config['sheetTitle']);

        $tableData = $reportData['data']['finalResults'][$config['tableKey']];

        [$sheetData, $periods, $disciplines] = self::preparePeriodsData(
            $tableData,
            $config['tableTitle'],
            $config['isPercentage']
        );

        if (empty($sheetData) || count($sheetData) <= 2) {
            return;
        }

        $sheet->fromArray($sheetData, null, 'A1');

        $totalColumns = count($sheetData[0]);
        $lastColumn = self::getColumnLetter($totalColumns - 1);

        self::formatPeriodsSheet($sheet, $lastColumn, $config['isPercentage']);

        $dataStartRow = 3;
        $disciplineCount = count($disciplines);

        $chart = self::createClusteredBarChart(
            $config['sheetTitle'],
            $config['chartTitle'],
            $dataStartRow,
            $lastColumn,
            $disciplines,
            $periods
        );

        $chartPosition = self::calculatePeriodsChartPosition($lastColumn, $disciplineCount, count($sheetData));
        $chart->setTopLeftPosition($chartPosition['topLeft']);
        $chart->setBottomRightPosition($chartPosition['bottomRight']);

        $sheet->addChart($chart);
    }

    /**
     * Подготавливает данные для таблицы с периодами
     *
     * @param array $tableData
     * @param string $tableTitle
     * @param bool $isPercentage
     * @return array [sheetData, periods, disciplines]
     */
    private static function preparePeriodsData(array $tableData, string $tableTitle, bool $isPercentage): array
    {
        $periods = [];

        foreach ($tableData as $row) {
            foreach ($row as $key => $value) {
                if ($key !== 'discipline' && !in_array($key, $periods)) {
                    $periods[] = $key;
                }
            }
        }

        if (empty($periods)) {
            return [[], [], []];
        }

        $headerRow1 = ['Дисциплина', $tableTitle];

        for ($i = 1; $i < count($periods); $i++) {
            $headerRow1[] = null;
        }

        $headerRow2 = [null, ...$periods];
        $disciplines = [];

        foreach ($tableData as $row) {
            $discipline = $row['discipline'];
            if (!isset($disciplines[$discipline])) {
                $disciplines[$discipline] = array_fill_keys($periods, self::DEFAULT_VALUE);
            }
            foreach ($row as $period => $value) {
                if ($period !== 'discipline') {
                    if ($isPercentage && $value !== null && $value !== self::DEFAULT_VALUE) {
                        $disciplines[$discipline][$period] = $value / 100;
                    } else {
                        $disciplines[$discipline][$period] = $value ?? self::DEFAULT_VALUE;
                    }
                }
            }
        }

        $dataRows = [];
        foreach ($disciplines as $discipline => $periodData) {
            $row = [$discipline];
            foreach ($periods as $period) {
                $row[] = $periodData[$period];
            }
            $dataRows[] = $row;
        }

        return [[$headerRow1, $headerRow2, ...$dataRows], $periods, $disciplines];
    }

    /**
     * Форматирует лист с данными по периодам
     *
     * @param Worksheet $sheet
     * @param string $lastColumn
     * @param bool $isPercentage
     */
    private static function formatPeriodsSheet(Worksheet $sheet, string $lastColumn, bool $isPercentage): void
    {
        $sheet->mergeCells("A1:A2");
        $sheet->mergeCells("B1:{$lastColumn}1");

        foreach (range('A', $lastColumn) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->getStyle("A1:{$lastColumn}2")->getFont()->setBold(true)->setSize(11);

        if ($isPercentage) {
            $percentageFormat = new Percentage();
            $lastRow = $sheet->getHighestRow();
            for ($i = 3; $i <= $lastRow; $i++) {
                $sheet->getStyle("B{$i}:{$lastColumn}{$i}")
                    ->getNumberFormat()
                    ->setFormatCode($percentageFormat);
            }
        }
    }

    /**
     * Создает простую диаграмму с одной серией данных
     *
     * @param string $sheetTitle
     * @param string $chartTitle
     * @param string $categoryRange
     * @param string $dataRange
     * @param int $rowCount
     * @param int $disciplineCount
     * @return Chart
     */
    private static function createBarChart(
        string $sheetTitle,
        string $chartTitle,
        string $categoryRange,
        string $dataRange,
        int $rowCount,
        int $disciplineCount
    ): Chart {
        $categories = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            "$sheetTitle!$categoryRange$rowCount",
            null,
            $disciplineCount
        );

        $values = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "$sheetTitle!$dataRange$rowCount",
            null,
            $disciplineCount
        );

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_STANDARD,
            [0],
            [],
            [$categories],
            [$values]
        );

        $xAxis = self::createXAxis();
        $yAxis = self::createYAxis();
        $layout = self::createChartLayout();

        return new Chart(
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
    }

    /**
     * Создает диаграмму с несколькими сериями данных (кластеризованную)
     *
     * @param string $sheetTitle
     * @param string $chartTitle
     * @param int $dataStartRow
     * @param string $lastColumn
     * @param array $disciplines
     * @param array $periods
     * @return Chart
     */
    private static function createClusteredBarChart(
        string $sheetTitle,
        string $chartTitle,
        int $dataStartRow,
        string $lastColumn,
        array $disciplines,
        array $periods
    ): Chart {
        $dataSeriesLabels = [];
        foreach ($disciplines as $discipline => $data) {
            $dataSeriesLabels[] = new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_STRING,
                "$sheetTitle!A" . ($dataStartRow + array_search($discipline, array_keys($disciplines))),
                null,
                1
            );
        }

        $xAxisTickValues = [
            new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_STRING,
                "$sheetTitle!B2:" . $lastColumn . "2",
                null,
                count($periods)
            )
        ];

        $dataSeriesValues = [];
        $row = $dataStartRow;
        foreach ($disciplines as $discipline => $data) {
            $dataSeriesValues[] = new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_NUMBER,
                "$sheetTitle!B" . $row . ":" . $lastColumn . $row,
                null,
                count($periods)
            );
            $row++;
        }

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($dataSeriesLabels) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );

        $legend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $title = new Title($chartTitle);
        $plotArea = new PlotArea(null, [$series]);

        return new Chart(
            'chart_' . $sheetTitle,
            $title,
            $legend,
            $plotArea,
            true,
            DataSeries::EMPTY_AS_GAP,
            new Title(''),
            new Title('')
        );
    }

    /**
     * Создает ось X для диаграммы
     *
     * @return Axis
     */
    private static function createXAxis(): Axis
    {
        $xAxis = new Axis();
        $xAxisText = new AxisText();
        $xAxisText->setRotation(-45);
        $xAxis->setAxisText($xAxisText);

        return $xAxis;
    }

    /**
     * Создает ось Y для диаграммы
     *
     * @return Axis
     */
    private static function createYAxis(): Axis
    {
        $yAxis = new Axis();
        $gridLines = new GridLines();
        $gridLines->setLineColorProperties('d9d9d9');
        $yAxis->setMajorGridlines($gridLines);

        return $yAxis;
    }

    /**
     * Создает макет диаграммы
     *
     * @return Layout
     */
    private static function createChartLayout(): Layout
    {
        $layout = new Layout();
        $layout->setShowVal(true);

        return $layout;
    }

    /**
     * Рассчитывает позицию и размер диаграммы
     *
     * @param int $disciplineCount Количество дисциплин
     * @param int $rowCount Общее количество строк в таблице
     * @param int $tableColumnsCount Количество колонок в таблице
     * @return array Массив с позициями верхнего левого и нижнего правого углов диаграммы
     */
    private static function calculateChartPosition(int $disciplineCount, int $rowCount, int $tableColumnsCount): array
    {
        $startColumnIndex = $tableColumnsCount + 1;
        $startColumn = self::getColumnLetter($startColumnIndex);

        $chartWidth = max(self::MIN_CHART_WIDTH_COLUMNS, $disciplineCount + 5);
        $endColumnIndex = $startColumnIndex + $chartWidth - 1;
        $endColumn = self::getColumnLetter($endColumnIndex);

        $endRow = max($rowCount, self::MIN_CHART_END_ROW);

        return [
            'topLeft' => $startColumn . self::CHART_START_ROW,
            'bottomRight' => $endColumn . $endRow
        ];
    }

    /**
     * Рассчитывает позицию для диаграммы с периодами
     *
     * @param string $lastColumn
     * @param int $disciplineCount
     * @param int $rowCount
     * @return array
     */
    private static function calculatePeriodsChartPosition(string $lastColumn, int $disciplineCount, int $rowCount): array
    {
        $chartStartColumn = chr(ord($lastColumn) + 2);
        $chartWidth = max(self::MIN_CHART_WIDTH_COLUMNS, $disciplineCount + 5);
        $chartEndColumn = chr(ord($chartStartColumn) + $chartWidth - 1);
        $chartEndRow = max($rowCount, self::MIN_CHART_END_ROW);

        return [
            'topLeft' => $chartStartColumn . self::CHART_START_ROW,
            'bottomRight' => $chartEndColumn . $chartEndRow
        ];
    }

    /**
     * Преобразует числовой индекс колонки в буквенное обозначение
     * Поддерживает колонки больше 'Z' (AA, AB и т.д.)
     *
     * @param int $columnIndex Индекс колонки (0-based)
     * @return string
     */
    private static function getColumnLetter(int $columnIndex): string
    {
        $columnLetter = '';
        $columnIndex++;

        while ($columnIndex > 0) {
            $modulo = ($columnIndex - 1) % 26;
            $columnLetter = chr(65 + $modulo) . $columnLetter;
            $columnIndex = (int)(($columnIndex - $modulo) / 26);
        }

        return $columnLetter;
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

        $columnCount = count($sheetData[0]);

        for ($i = 0; $i < $columnCount; $i++) {
            $columnLetter = self::getColumnLetter($i);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        $lastColumnLetter = self::getColumnLetter($columnCount - 1);
        $sheet->getStyle("A1:{$lastColumnLetter}1")->getFont()->setBold(true)->setSize(11);

        for ($i = 2; $i <= count($sheetData); $i++) {
            if ($sheetData[$i-1][1] !== self::DEFAULT_VALUE) {
                $percentageFormat = new Percentage();
                $sheet->getCell('B' . $i)
                    ->getStyle()->getNumberFormat()
                    ->setFormatCode($percentageFormat);
            }
        }
    }
}
