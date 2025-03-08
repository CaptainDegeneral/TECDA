<?php

namespace App\Exports;

use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
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

/**
 * Класс для экспорта отчетов в формат Excel.
 */
class ExcelReportExporter
{
    /**
     * @var array Настройки конфигурации экспорта
     */
    private array $config;

    /**
     * @var Spreadsheet Экземпляр Spreadsheet
     */
    private Spreadsheet $spreadsheet;

    /**
     * @var array Данные отчета
     */
    private array $reportData;

    /**
     * @var string Значение по умолчанию для пустых ячеек
     */
    private string $defaultValue = '-';

    /**
     * Конструктор класса
     *
     * @param array $config Настройки для экспорта (опционально)
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'layout' => [
                'minChartWidthColumns' => 10,
                'minChartEndRow' => 25,
                'chartStartRow' => 2,
            ],
            'chartTypes' => [
                'quality' => [
                    'sheetTitle' => 'QualityResults',
                    'chartTitle' => 'Уровень качества знаний',
                    'dataRange' => '$B$2:$B$',
                    'categoryRange' => '$A$2:$A$',
                ],
                'averageScore' => [
                    'sheetTitle' => 'AverageScoreResults',
                    'chartTitle' => 'Средний балл',
                    'dataRange' => '$C$2:$C$',
                    'categoryRange' => '$A$2:$A$',
                ],
            ],
            'periodsChartTypes' => [
                'quality' => [
                    'sheetTitle' => 'QualityPerPeriods',
                    'chartTitle' => 'Уровень качества знаний по годам',
                    'isPercentage' => true,
                    'tableTitle' => 'Уровень качества знаний',
                    'tableKey' => 'qualityTable',
                ],
                'averageScore' => [
                    'sheetTitle' => 'AverageScorePerPeriods',
                    'chartTitle' => 'Средний балл по годам',
                    'isPercentage' => false,
                    'tableTitle' => 'Средний балл',
                    'tableKey' => 'averageScoreTable',
                ],
            ],
            'overallTablesHeaders' => [
                'discipline' => 'Дисциплина',
                'quality' => 'Уровень качества знаний',
                'averageScore' => 'Средний балл',
            ],
        ], $config);

        $this->initSpreadsheet();
    }

    /**
     * Инициализация объекта Spreadsheet
     */
    private function initSpreadsheet(): void
    {
        $this->spreadsheet = new Spreadsheet();
    }

    /**
     * Экспортирует данные отчета в Excel-документ с 4 листами.
     *
     * @param array $reportData Данные для отчета
     * @return string Путь к временному файлу
     * @throws Exception При ошибке создания Excel-файла
     * @throws InvalidArgumentException При некорректной структуре данных
     */
    public function export(array $reportData): string
    {
        $this->reportData = $reportData;
        $this->validateReportData();

        foreach ($this->config['chartTypes'] as $type => $config) {
            $this->createChartResultsSheet(
                $config['sheetTitle'],
                $config['chartTitle'],
                $config['dataRange'],
                $config['categoryRange']
            );
        }

        foreach ($this->config['periodsChartTypes'] as $type => $config) {
            $this->createPeriodsResultsSheet($config);
        }

        $this->spreadsheet->removeSheetByIndex(0);
        $this->spreadsheet->setActiveSheetIndex(0);

        return $this->saveDocument();
    }

    /**
     * Валидирует структуру данных отчета
     *
     * @throws InvalidArgumentException При некорректной структуре данных
     */
    private function validateReportData(): void
    {
        if (
            empty($this->reportData['data']['overallResults']) ||
            empty($this->reportData['data']['finalResults']['qualityTable']) ||
            empty($this->reportData['data']['finalResults']['averageScoreTable'])
        ) {
            throw new InvalidArgumentException('Invalid report data structure: missing required data fields');
        }
    }

    /**
     * Сохраняет документ во временный файл
     *
     * @return string Путь к временному файлу
     * @throws Exception При ошибке сохранения файла
     */
    private function saveDocument(): string
    {
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'PHPSpreadsheet');
            $writer = new Xlsx($this->spreadsheet);
            $writer->setIncludeCharts(true);
            $writer->save($tempFile);
            return $tempFile;
        } catch (Throwable $e) {
            throw new Exception('Failed to create Excel report: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Создает лист с таблицей данных и диаграммой для общих метрик
     *
     * @param string $sheetTitle Название листа
     * @param string $chartTitle Заголовок диаграммы
     * @param string $dataRange Диапазон данных для диаграммы
     * @param string $categoryRange Диапазон категорий для диаграммы
     */
    private function createChartResultsSheet(
        string $sheetTitle,
        string $chartTitle,
        string $dataRange,
        string $categoryRange
    ): void {
        $sheet = $this->spreadsheet->createSheet();
        $sheet->setTitle($sheetTitle);

        $sheetData = $this->prepareSheetData($this->reportData['data']['overallResults']);
        if (empty($sheetData)) {
            $sheet->setSelectedCell('A1');
            return;
        }

        $this->fillSheetWithData($sheet, $sheetData);

        if (count($sheetData) <= 1) {
            $sheet->setSelectedCell('A1');
            return;
        }

        $rowCount = count($sheetData);
        $disciplineCount = $rowCount - 1;
        $tableColumnsCount = count($sheetData[0]);

        $chartPosition = $this->calculateChartPosition($disciplineCount, $rowCount, $tableColumnsCount);

        $chart = $this->createBarChart(
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
        $sheet->setSelectedCell('A1');
    }

    /**
     * Создает лист с таблицей данных и диаграммой по периодам
     *
     * @param array $config Конфигурация листа и диаграммы
     */
    private function createPeriodsResultsSheet(array $config): void
    {
        $sheet = $this->spreadsheet->createSheet();
        $sheet->setTitle($config['sheetTitle']);

        $tableData = $this->reportData['data']['finalResults'][$config['tableKey']];
        if (empty($tableData)) {
            $sheet->setSelectedCell('A1');
            return;
        }

        [$sheetData, $periods, $disciplines] = $this->preparePeriodsData(
            $tableData,
            $config['tableTitle'],
            $config['isPercentage']
        );

        if (empty($sheetData) || count($sheetData) <= 2) {
            $sheet->setSelectedCell('A1');
            return;
        }

        $sheet->fromArray($sheetData, null, 'A1');

        $totalColumns = count($sheetData[0]);
        $lastColumn = $this->getColumnLetter($totalColumns - 1);

        $this->formatPeriodsSheet($sheet, $lastColumn, $config['isPercentage']);

        $dataStartRow = 3;
        $disciplineCount = count($disciplines);
        $periodCount = count($periods); // Добавляем количество периодов

        $chart = $this->createClusteredBarChart(
            $config['sheetTitle'],
            $config['chartTitle'],
            $dataStartRow,
            $lastColumn,
            $disciplines,
            $periods
        );

        $chartPosition = $this->calculatePeriodsChartPosition($lastColumn, $disciplineCount, count($sheetData), $periodCount);
        $chart->setTopLeftPosition($chartPosition['topLeft']);
        $chart->setBottomRightPosition($chartPosition['bottomRight']);

        $sheet->addChart($chart);
        $sheet->setSelectedCell('A1');
    }

    /**
     * Подготавливает данные для таблицы с периодами
     *
     * @param array $tableData Данные таблицы
     * @param string $tableTitle Заголовок таблицы
     * @param bool $isPercentage Флаг форматирования как проценты
     * @return array [sheetData, periods, disciplines]
     */
    private function preparePeriodsData(array $tableData, string $tableTitle, bool $isPercentage): array
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
                $disciplines[$discipline] = array_fill_keys($periods, $this->defaultValue);
            }
            foreach ($row as $period => $value) {
                if ($period !== 'discipline') {
                    if ($isPercentage && $value !== null && $value !== $this->defaultValue) {
                        $disciplines[$discipline][$period] = $value / 100;
                    } else {
                        $disciplines[$discipline][$period] = $value ?? $this->defaultValue;
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
     * @param Worksheet $sheet Лист Excel
     * @param string $lastColumn Последняя колонка
     * @param bool $isPercentage Флаг форматирования как проценты
     */
    private function formatPeriodsSheet(Worksheet $sheet, string $lastColumn, bool $isPercentage): void
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
     * @param string $sheetTitle Название листа
     * @param string $chartTitle Заголовок диаграммы
     * @param string $categoryRange Диапазон категорий
     * @param string $dataRange Диапазон данных
     * @param int $rowCount Количество строк
     * @param int $disciplineCount Количество дисциплин
     * @return Chart Созданная диаграмма
     */
    private function createBarChart(
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

        $xAxis = $this->createXAxis();
        $yAxis = $this->createYAxis();
        $layout = $this->createChartLayout();

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
     * @param string $sheetTitle Название листа
     * @param string $chartTitle Заголовок диаграммы
     * @param int $dataStartRow Начальная строка данных
     * @param string $lastColumn Последняя колонка
     * @param array $disciplines Список дисциплин
     * @param array $periods Список периодов
     * @return Chart Созданная диаграмма
     */
    private function createClusteredBarChart(
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
                "$sheetTitle!B2:{$lastColumn}2",
                null,
                count($periods)
            )
        ];

        $dataSeriesValues = [];
        $row = $dataStartRow;
        foreach ($disciplines as $discipline => $data) {
            $dataSeriesValues[] = new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_NUMBER,
                "$sheetTitle!B{$row}:{$lastColumn}{$row}",
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
     * @return Axis Созданная ось X
     */
    private function createXAxis(): Axis
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
     * @return Axis Созданная ось Y
     */
    private function createYAxis(): Axis
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
     * @return Layout Созданный макет
     */
    private function createChartLayout(): Layout
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
    private function calculateChartPosition(int $disciplineCount, int $rowCount, int $tableColumnsCount): array
    {
        $startColumnIndex = $tableColumnsCount + 1;
        $startColumn = $this->getColumnLetter($startColumnIndex);

        $chartWidth = max($this->config['layout']['minChartWidthColumns'], $disciplineCount + 5);
        $endColumnIndex = $startColumnIndex + $chartWidth - 1;
        $endColumn = $this->getColumnLetter($endColumnIndex);

        $endRow = max($rowCount, $this->config['layout']['minChartEndRow']);

        return [
            'topLeft' => $startColumn . $this->config['layout']['chartStartRow'],
            'bottomRight' => $endColumn . $endRow
        ];
    }

    /**
     * Рассчитывает позицию для диаграммы с периодами
     *
     * @param string $lastColumn Последняя колонка таблицы
     * @param int $disciplineCount Количество дисциплин
     * @param int $rowCount Количество строк
     * @param int $periodCount Количество периодов
     * @return array Массив с позициями верхнего левого и нижнего правого углов диаграммы
     */
    private function calculatePeriodsChartPosition(string $lastColumn, int $disciplineCount, int $rowCount, int $periodCount): array
    {
        $startColumnIndex = ord($lastColumn) - 65 + 2;
        $chartStartColumn = $this->getColumnLetter($startColumnIndex);
        $tableWidth = $periodCount + 1;
        $chartWidth = max($this->config['layout']['minChartWidthColumns'], $tableWidth + $disciplineCount);
        $endColumnIndex = $startColumnIndex + $chartWidth - 1;
        $chartEndColumn = $this->getColumnLetter($endColumnIndex);
        $chartEndRow = max($rowCount, $this->config['layout']['minChartEndRow']);

        return [
            'topLeft' => $chartStartColumn . $this->config['layout']['chartStartRow'],
            'bottomRight' => $chartEndColumn . $chartEndRow
        ];
    }

    /**
     * Преобразует числовой индекс колонки в буквенное обозначение
     *
     * @param int $columnIndex Индекс колонки (0-based)
     * @return string Буквенное обозначение колонки
     */
    private function getColumnLetter(int $columnIndex): string
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
     * @param array $overallResults Данные общих результатов
     * @return array Подготовленные данные для таблицы
     */
    private function prepareSheetData(array $overallResults): array
    {
        $sheetData = [
            [
                $this->config['overallTablesHeaders']['discipline'],
                $this->config['overallTablesHeaders']['quality'],
                $this->config['overallTablesHeaders']['averageScore']
            ]
        ];

        foreach ($overallResults as $row) {
            $quality = $row['avgQuality'] !== null ? $row['avgQuality'] / 100 : $this->defaultValue;
            $sheetData[] = [
                $row['discipline'],
                $quality,
                $row['avgAverageScore'] ?? $this->defaultValue,
            ];
        }

        return $sheetData;
    }

    /**
     * Заполняет лист данными и применяет форматирование
     *
     * @param Worksheet $sheet Лист Excel
     * @param array $sheetData Данные для заполнения
     */
    private function fillSheetWithData(Worksheet $sheet, array $sheetData): void
    {
        $sheet->fromArray($sheetData, null, 'A1');

        $columnCount = count($sheetData[0]);

        for ($i = 0; $i < $columnCount; $i++) {
            $columnLetter = $this->getColumnLetter($i);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        $lastColumnLetter = $this->getColumnLetter($columnCount - 1);
        $sheet->getStyle("A1:{$lastColumnLetter}1")->getFont()->setBold(true)->setSize(11);

        for ($i = 2; $i <= count($sheetData); $i++) {
            if ($sheetData[$i - 1][1] !== $this->defaultValue) {
                $percentageFormat = new Percentage();
                $sheet->getCell("B{$i}")
                    ->getStyle()->getNumberFormat()
                    ->setFormatCode($percentageFormat);
            }
        }
    }

    /**
     * Фабричный метод для создания экспортера с настройками по умолчанию
     *
     * @return self Новый экземпляр экспортера
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Фабричный метод для экспорта с настройками по умолчанию
     *
     * @param array $reportData Данные отчета
     * @return string Путь к временному файлу
     * @throws Exception При ошибке создания файла
     */
    public static function exportDefault(array $reportData): string
    {
        return new self()->export($reportData);
    }
}
