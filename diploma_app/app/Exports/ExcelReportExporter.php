<?php

namespace App\Exports;

use App\Contracts\ReportExporterInterface;
use App\Services\ReportDataValidationService;
use Exception;
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
 *
 * Создает Excel-документ с таблицами и диаграммами на основе данных отчета.
 * Использует библиотеку PhpSpreadsheet. Валидация данных выполняется с помощью сервиса
 *  `ReportDataValidationService`.
 *
 * @property array $config Конфигурация стилей и параметров
 * @property array $reportData Данные отчета
 * @property ReportDataValidationService $validator Сервис валидации
 * @property Spreadsheet $spreadsheet Экземпляр Spreadsheet
 * @property string $defaultValue Значение по умолчанию ('-')
 */
class ExcelReportExporter implements ReportExporterInterface
{
    private array $config;
    private array $reportData;
    private ReportDataValidationService $validator;
    private Spreadsheet $spreadsheet;
    private string $defaultValue = '-';

    /**
     * Конструктор класса
     *
     * @param array $config Настройки для экспорта (опционально)
     */
    public function __construct(ReportDataValidationService $validator, array $config = [])
    {
        $this->validator = $validator;

        $this->config = array_merge(config('excel', []), $config);

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
        $this->validator->validate($reportData);

        foreach ($this->config['chartTypes'] as $config) {
            $this->createChartResultsSheet(
                $config['sheetTitle'],
                $config['chartTitle'],
                $config['dataRange'],
                $config['categoryRange']
            );
        }

        foreach ($this->config['periodsChartTypes'] as $config) {
            $this->createPeriodsResultsSheet($config);
        }

        $this->spreadsheet->removeSheetByIndex(0);
        $this->spreadsheet->setActiveSheetIndex(0);

        return $this->saveDocument();
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

        $sheet->fromArray($sheetData);

        $totalColumns = count($sheetData[0]);
        $lastColumn = $this->getColumnLetter($totalColumns - 1);

        $this->formatPeriodsSheet($sheet, $lastColumn, $config['isPercentage']);

        $dataStartRow = $this->config['chartSettings']['dataStartRow'];
        $disciplineCount = count($disciplines);
        $periodCount = count($periods);

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

        $headerRow1 = [$this->config['dataProcessing']['disciplineColumnTitle'], $tableTitle];
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
                        $disciplines[$discipline][$period] = $value / $this->config['dataProcessing']['qualityDivider'];
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

        $headerStyle = $this->config['styles']['header'];
        $sheet->getStyle("A1:{$lastColumn}2")
            ->getFont()
            ->setBold($headerStyle['font']['bold'])
            ->setSize($headerStyle['font']['size']);
        $sheet->getStyle("A1:{$lastColumn}2")
            ->getAlignment()
            ->setHorizontal($headerStyle['alignment']['horizontal'])
            ->setVertical($headerStyle['alignment']['vertical']);

        if ($isPercentage) {
            $percentageFormat = new Percentage();
            $lastRow = $sheet->getHighestRow();
            for ($i = 3; $i <= $lastRow; $i++) {
                $sheet->getStyle("B$i:$lastColumn$i")
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
            $this->config['chartSettings']['barChartName'],
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
        foreach ($disciplines as $ignored) {
            $dataSeriesValues[] = new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_NUMBER,
                "$sheetTitle!B$row:$lastColumn$row",
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

        $legend = new Legend(
            $this->config['chartSettings']['legend']['position'],
            null,
            $this->config['chartSettings']['legend']['showBorder']
        );
        $title = new Title($chartTitle);
        $plotArea = new PlotArea(null, [$series]);

        return new Chart(
            $this->config['chartSettings']['clusteredChartNamePrefix'] . $sheetTitle,
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
        $xAxisText->setRotation($this->config['chartSettings']['xAxis']['textRotation']);
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
        $gridLines->setLineColorProperties($this->config['chartSettings']['yAxis']['gridLineColor']);
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
        $layout->setShowVal($this->config['chartSettings']['layout']['showValues']);

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

        $chartWidth = max($this->config['layout']['minChartWidthColumns'], $disciplineCount + $this->config['layout']['chartWidthPadding']);
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
        $startColumnIndex = ord($lastColumn) - 65 + $this->config['layout']['chartColumnOffset'];
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
            $quality = $row['avgQuality'] !== null ? $row['avgQuality'] / $this->config['dataProcessing']['qualityDivider'] : $this->defaultValue;
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
        $sheet->fromArray($sheetData);

        $columnCount = count($sheetData[0]);

        for ($i = 0; $i < $columnCount; $i++) {
            $columnLetter = $this->getColumnLetter($i);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        $lastColumnLetter = $this->getColumnLetter($columnCount - 1);
        $headerStyle = $this->config['styles']['header'];
        $sheet->getStyle("A1:{$lastColumnLetter}1")
            ->getFont()
            ->setBold($headerStyle['font']['bold'])
            ->setSize($headerStyle['font']['size']);
        $sheet->getStyle("A1:{$lastColumnLetter}1")
            ->getAlignment()
            ->setHorizontal($headerStyle['alignment']['horizontal'])
            ->setVertical($headerStyle['alignment']['vertical']);

        for ($i = 2; $i <= count($sheetData); $i++) {
            if ($sheetData[$i - 1][1] !== $this->defaultValue) {
                $percentageFormat = new Percentage();
                $sheet->getCell("B$i")
                    ->getStyle()->getNumberFormat()
                    ->setFormatCode($percentageFormat);
            }
        }
    }

    /**
     * Фабричный метод для создания экспортера с настройками по умолчанию
     *
     * @param ReportDataValidationService $validator Валидатор данных
     * @return self Новый экземпляр экспортера
     */
    public static function create(ReportDataValidationService $validator): self
    {
        return new self($validator);
    }

    /**
     * Фабричный метод для экспорта с настройками по умолчанию
     *
     * @param ReportDataValidationService $validator Валидатор данных
     * @param array $reportData Данные отчета
     * @return string Путь к временному файлу
     * @throws Exception При ошибке создания файла
     */
    public static function exportDefault(ReportDataValidationService $validator, array $reportData): string
    {
        return new self($validator)->export($reportData);
    }
}
