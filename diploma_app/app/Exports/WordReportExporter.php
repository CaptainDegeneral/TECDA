<?php

namespace App\Exports;

use App\Abstracts\ReportDataValidator;
use InvalidArgumentException;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Exception\Exception as PhpWordException;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Tab;

/**
 * Класс для экспорта отчетов в формат Word.
 */
class WordReportExporter extends ReportDataValidator
{
    /**
     * @var array Настройки конфигурации экспорта
     */
    private array $config;

    /**
     * @var PhpWord Экземпляр PhpWord
     */
    private PhpWord $phpWord;

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
            'styles' => [
                'title' => [
                    'bold' => true,
                    'size' => 14,
                    'name' => 'Times New Roman'
                ],
                'subtitle' => [
                    'size' => 12,
                    'name' => 'Times New Roman'
                ],
                'table' => [
                    'borderSize' => 6,
                    'borderColor' => '000000',
                    'cellMargin' => 80,
                    'width' => 9800,
                    'unit' => 'dxa',
                ],
                'header' => [
                    'bold' => true,
                    'valign' => 'center',
                    'align' => 'center',
                    'size' => 11,
                ],
                'cell' => [
                    'valign' => 'center',
                    'size' => 11,
                ],
                'description' => [
                    'font' => [
                        'size' => 11,
                        'name' => 'Times New Roman'
                    ],
                    'paragraph' => [
                        'alignment' => Jc::BOTH,
                        'indentation' => [
                            'firstLine' => 425.25
                        ],
                        'tabs' => [
                            [
                                'type' => 'left',
                                'position' => 850.
                            ]
                        ],
                    ],
                ],
                'averageScoreText' => [
                    'font' => [
                        'size' => 11,
                        'name' => 'Times New Roman',
                        'lineHeight' => 1.0,
                        'spaceBefore' => 0,
                        'spaceAfter' => 0
                    ],
                    'paragraph' => [
                        'indentation' => [
                            'firstLine' => 425.25
                        ],
                        'alignment' => Jc::BOTH,
                    ],
                ],
                'qualityText' => [
                    'font' => [
                        'size' => 11,
                        'name' => 'Times New Roman',
                        'lineHeight' => 1.0,
                        'spaceBefore' => 0,
                        'spaceAfter' => 0
                    ],
                    'paragraph' => [
                        'indentation' => [
                            'firstLine' => 425.25
                        ],
                        'alignment' => Jc::BOTH,
                    ],
                ],
            ],
            'tableConfig' => [
                'disciplineWidth' => 4000,
            ],
            'text' => [
                'title' => 'Результаты промежуточной аттестации',
                'teacherPrefix' => 'Преподаватель ',
                'description' => 'Результаты освоения обучающимися образовательных программ по итогам мониторингов, проводимых организацией (качество знаний с учетом статуса образовательной организации)',
                'averageScore' => 'По результатам промежуточной аттестации за межаттестационный период средний балл обучающихся составил:',
                'quality' => 'По результатам промежуточной аттестации за межаттестационный период качество знаний обучающихся составило:',
            ],
            'margins' => [
                'left' => 1200,
                'right' => 900,
                'top' => 900,
                'bottom' => 900,
            ],
            'numberingStyle' => [
                'type' => 'multilevel',
                'levels' => [
                    [
                        'format' => 'decimal',
                        'text' => '%1.',
                        'left' => 360,
                        'hanging' => 360
                    ],
                    [
                        'format' => 'decimal',
                        'text' => '%1.%2.',
                        'left' => 480,
                        'hanging' => 480
                    ],
                ],
            ],
            'emptyLine' => [
                'defaultHeight' => 1.15,
                'tableSpacer' => 1.0,
            ],
        ], $config);

        $this->initPhpWord();
    }

    /**
     * Инициализация объекта PhpWord с базовыми настройками
     */
    private function initPhpWord(): void
    {
        $this->phpWord = new PhpWord();
        $this->phpWord->setDefaultParagraphStyle(['spaceBefore' => 0, 'spaceAfter' => 0]);
        $this->phpWord->setDefaultFontName('Times New Roman');
        $this->phpWord->setDefaultFontSize(11);
        $this->phpWord->addNumberingStyle('multilevel', $this->config['numberingStyle']);
    }

    /**
     * Экспортирует данные отчета в Word-документ.
     *
     * @param array $reportData Данные для отчета
     * @return string Путь к временному файлу
     * @throws PhpWordException
     * @throws InvalidArgumentException
     */
    public function export(array $reportData): string
    {
        $this->reportData = $reportData;
        $this->validateReportData();
        $section = $this->phpWord->addSection($this->config['margins']);
        $this->generateReport($section);

        return $this->saveDocument();
    }

    /**
     * Генерирует структуру отчета
     *
     * @param Section $section Секция документа
     */
    private function generateReport(Section $section): void
    {
        $finalResults = $this->reportData['data']['finalResults'];

        $this->addTitle($section);

        $this->addTeacherName($section);

        $this->addEmptyLine($section, $this->config['emptyLine']['defaultHeight']);

        $this->addDescription($section);

        $this->addEmptyLine($section, $this->config['emptyLine']['defaultHeight']);

        $this->addAverageScoreText($section);

        $this->addEmptyLine($section, $this->config['emptyLine']['tableSpacer']);

        $this->addGenericTable($section, $finalResults['averageScoreTable'], 'Средний бал');

        $this->addEmptyLine($section, $this->config['emptyLine']['defaultHeight']);

        $this->addQualityText($section);

        $this->addEmptyLine($section, $this->config['emptyLine']['tableSpacer']);

        $this->addGenericTable($section, $finalResults['qualityTable'], 'Уровень качества знаний, %');
    }

    /**
     * Сохраняет документ во временный файл
     *
     * @return string Путь к временному файлу
     * @throws PhpWordException
     */
    private function saveDocument(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');

        $writer = IOFactory::createWriter($this->phpWord);
        $writer->save($tempFile);

        return $tempFile;
    }

    /**
     * Форматирует ФИО преподавателя
     *
     * @return string Отформатированное ФИО
     */
    private function formatTeacherName(): string
    {
        $user = $this->reportData['user'] ?? null;
        if ($user && isset($user['last_name'], $user['name'], $user['surname'])) {
            $initials = mb_substr($user['name'], 0, 1) . '.' . mb_substr($user['surname'], 0, 1) . '.';
            return $user['last_name'] . ' ' . $initials;
        }
        return 'Шинакова С.В.';
    }

    /**
     * Добавляет таблицу с данными
     *
     * @param Section $section Секция документа
     * @param array $data Данные для таблицы
     * @param string $tableHeaderText Заголовок таблицы
     */
    private function addGenericTable(Section $section, array $data, string $tableHeaderText): void
    {
        if (empty($data)) {
            return;
        }

        $table = $section->addTable($this->config['styles']['table']);
        $allKeys = array_keys($data[0]);
        $disciplineKey = 'discipline';
        $yearKeys = array_filter($allKeys, fn($key) => $key !== $disciplineKey);
        $yearCount = count($yearKeys);

        $headerRow = $table->addRow();
        $headerRow->addCell(
            $this->config['tableConfig']['disciplineWidth'],
            array_merge($this->config['styles']['header'], ['vMerge' => 'restart'])
        )->addText('Дисциплина', $this->config['styles']['header']);

        $headerCell = $headerRow->addCell(
            $this->config['styles']['table']['width'] - $this->config['tableConfig']['disciplineWidth'],
            array_merge($this->config['styles']['header'], ['gridSpan' => $yearCount, 'vMerge' => 'continue'])
        );
        $headerCell->addText($tableHeaderText, $this->config['styles']['header']);

        $subHeaderRow = $table->addRow();
        $subHeaderRow->addCell(null, ['vMerge' => 'continue']);
        $valueWidth = ($this->config['styles']['table']['width'] - $this->config['tableConfig']['disciplineWidth']) / $yearCount;

        foreach ($yearKeys as $year) {
            $subHeaderRow->addCell($valueWidth, $this->config['styles']['header'])
                ->addText($year, $this->config['styles']['header']);
        }

        foreach ($data as $row) {
            $dataRow = $table->addRow();
            $dataRow->addCell($this->config['tableConfig']['disciplineWidth'], $this->config['styles']['cell'])
                ->addText($row[$disciplineKey] ?? $this->defaultValue, $this->config['styles']['cell']);

            foreach ($yearKeys as $year) {
                $value = $row[$year] ?? $this->defaultValue;
                $dataRow->addCell($valueWidth, $this->config['styles']['cell'])
                    ->addText($value, $this->config['styles']['cell']);
            }
        }
    }

    /**
     * Добавляет заголовок отчета
     *
     * @param Section $section Секция документа
     */
    private function addTitle(Section $section): void
    {
        $section->addText(
            $this->config['text']['title'],
            $this->config['styles']['title'],
            ['alignment' => 'center']
        );
    }

    /**
     * Добавляет имя преподавателя
     *
     * @param Section $section Секция документа
     */
    private function addTeacherName(Section $section): void
    {
        $teacherName = $this->formatTeacherName();
        $section->addText(
            $this->config['text']['teacherPrefix'] . $teacherName,
            $this->config['styles']['subtitle'],
            ['alignment' => 'center']
        );
    }

    /**
     * Добавляет описание отчета
     *
     * @param Section $section Секция документа
     */
    private function addDescription(Section $section): void
    {
        $descConfig = $this->config['styles']['description'];
        $fontStyle = $descConfig['font'];
        $paragraphStyle = $descConfig['paragraph'];
        $tabs = array_map(fn($tab) => new Tab($tab['type'], $tab['position']), $paragraphStyle['tabs']);
        $paragraphStyle['tabs'] = $tabs;

        $section->addListItem(
            $this->config['text']['description'],
            1,
            $fontStyle,
            'multilevel',
            $paragraphStyle
        );
    }

    /**
     * Добавляет текст о среднем балле
     *
     * @param Section $section Секция документа
     */
    private function addAverageScoreText(Section $section): void
    {
        $avgConfig = $this->config['styles']['averageScoreText'];
        $section->addText($this->config['text']['averageScore'], $avgConfig['font'], $avgConfig['paragraph']);
    }

    /**
     * Добавляет текст о качестве знаний
     *
     * @param Section $section Секция документа
     */
    private function addQualityText(Section $section): void
    {
        $qualityConfig = $this->config['styles']['qualityText'];
        $section->addText($this->config['text']['quality'], $qualityConfig['font'], $qualityConfig['paragraph']);
    }

    /**
     * Добавляет пустую строку с указанной высотой
     *
     * @param Section $section Секция документа
     * @param float $lineHeight Высота строки
     */
    private function addEmptyLine(Section $section, float $lineHeight): void
    {
        $section->addText('', ['lineHeight' => $lineHeight]);
    }

    /**
     * Фабричный метод для создания экспортера с настройками по умолчанию
     *
     * @return self
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
     * @throws PhpWordException
     */
    public static function exportDefault(array $reportData): string
    {
        return new self()->export($reportData);
    }
}
