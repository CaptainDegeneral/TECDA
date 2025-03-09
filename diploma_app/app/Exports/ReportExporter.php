<?php

namespace App\Exports;

use App\Contracts\ArchiveServiceInterface;
use App\Contracts\ReportExporterInterface;
use App\Services\ArchiveService;
use App\Services\ReportDataValidationService;
use Exception;
use PhpOffice\PhpWord\Exception\Exception as PhpWordException;
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Класс для экспорта отчетов в различные форматы с последующей архивацией в ZIP.
 *
 * Этот класс координирует создание отчетов в заданных форматах (например, Word и Excel),
 * их упаковку в ZIP-архив и предоставление пользователю возможности скачать результат.
 * Поддерживает гибкую конфигурацию и обработку ошибок.
 *
 * @property ArchiveServiceInterface $archiveService Сервис для работы с архивами
 * @property ReportExporterInterface $wordExporter Сервис для экспорта в Word
 * @property ReportExporterInterface $excelExporter Сервис для экспорта в Excel
 * @property array $config Конфигурация экспорта
 */
class ReportExporter
{
    /**
     * @var ArchiveServiceInterface Сервис для работы с архивами
     */
    private ArchiveServiceInterface $archiveService;

    /**
     * @var ReportExporterInterface Сервис для экспорта в Word
     */
    private ReportExporterInterface $wordExporter;

    /**
     * @var ReportExporterInterface Сервис для экспорта в Excel
     */
    private ReportExporterInterface $excelExporter;

    /**
     * @var array Конфигурация экспорта
     */
    private array $config;

    /**
     * Конструктор класса.
     *
     * Инициализирует объект с зависимостями и конфигурацией.
     *
     * @param ArchiveServiceInterface $archiveService Сервис для создания ZIP-архивов
     * @param ReportExporterInterface $wordExporter Объект для экспорта отчетов в Word
     * @param ReportExporterInterface $excelExporter Объект для экспорта отчетов в Excel
     * @param array $config Конфигурация экспорта (опционально)
     */
    public function __construct(
        ArchiveServiceInterface $archiveService,
        ReportExporterInterface $wordExporter,
        ReportExporterInterface $excelExporter,
        array $config = []
    ) {
        $this->archiveService = $archiveService;
        $this->wordExporter = $wordExporter;
        $this->excelExporter = $excelExporter;
        $this->config = array_merge([
            'archiveNameFormat' => '{reportType}_{lastName}_{timestamp}.zip',
            'reportType' => 'Отчет',
            'defaultLastName' => 'Unknown',
            'tempDir' => sys_get_temp_dir(),
        ], $config);
    }

    /**
     * Экспортирует данные отчета в Word и Excel, архивирует их в ZIP и возвращает файл для скачивания.
     *
     * Метод создает временные файлы отчетов, упаковывает их в ZIP-архив и возвращает
     * HTTP-ответ для скачивания. После отправки файл автоматически удаляется.
     *
     * @param array $reportData Данные отчета для экспорта
     * @param string $reportTitle Заголовок отчета, используемый для именования файлов
     * @return BinaryFileResponse Ответ с ZIP-архивом для скачивания
     * @throws Exception Если произошла ошибка во время экспорта или архивации
     */
    public function export(array $reportData, string $reportTitle): BinaryFileResponse
    {
        try {
            $wordFile = $this->wordExporter->export($reportData);
            $excelFile = $this->excelExporter->export($reportData);

            $zipFilename = $this->createTempZipFilePath();
            $files = [
                ['path' => $wordFile, 'name' => $this->cleanReportTitle($reportTitle) . '.docx'],
                ['path' => $excelFile, 'name' => $this->cleanReportTitle($reportTitle) . '.xlsx'],
            ];
            $this->archiveService->createZip($files, $zipFilename);

            $archiveName = $this->generateArchiveName($reportData);

            return response()->download($zipFilename, $archiveName)->deleteFileAfterSend();
        } catch (PhpWordException $e) {
            throw new Exception(
                'Ошибка при создании Word-документа: ' . $e->getMessage(),
                0,
                $e
            );
        } catch (PhpSpreadsheetException $e) {
            throw new Exception(
                'Ошибка при создании Excel-документа: ' . $e->getMessage(),

                0, $e
            );
        } catch (Exception $e) {
            throw new Exception(
                'Ошибка при экспорте отчета: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Очищает заголовок отчета для безопасного использования в именах файлов.
     *
     * Удаляет специальные символы, заменяет пробелы на подчеркивания и форматирует даты.
     *
     * @param string $reportTitle Исходный заголовок отчета
     * @return string Очищенный заголовок
     */
    private function cleanReportTitle(string $reportTitle): string
    {
        $cleanTitle = trim($reportTitle);
        $cleanTitle = preg_replace('/[^А-яa-zA-Z0-9\s]+/u', ' ', $cleanTitle);
        $cleanTitle = preg_replace('/\s+/u', '_', $cleanTitle);

        if (preg_match('/_от_(\d{2}_\d{2}_\d{4})/', $cleanTitle, $matches)) {
            $date = str_replace('_', '-', $matches[1]);
            $cleanTitle = str_replace($matches[0], '_от_' . $date, $cleanTitle);
        }

        return $cleanTitle;
    }

    /**
     * Генерирует имя ZIP-архива на основе данных отчета.
     *
     * Использует шаблон из конфигурации и данные пользователя (если доступны).
     *
     * @param array $reportData Данные отчета
     * @return string Имя архива
     */
    private function generateArchiveName(array $reportData): string
    {
        $lastName = $reportData['user']['last_name'] ?? $this->config['defaultLastName'];
        $cleanLastName = $this->cleanReportTitle($lastName);
        $timestamp = time();

        return str_replace(
            ['{reportType}', '{lastName}', '{timestamp}'],
            [$this->config['reportType'], $cleanLastName, $timestamp],
            $this->config['archiveNameFormat']
        );
    }

    /**
     * Создает путь для временного ZIP-файла в директории storage/app/temp.
     *
     * Использует Laravel storage_path для создания уникального имени файла в заданной директории.
     * Если директория не существует, она будет создана.
     *
     * @return string Путь к временному файлу
     * @throws Exception Если не удалось создать директорию
     */
    private function createTempZipFilePath(): string
    {
        $tempDir = storage_path('app/temp');

        if (!file_exists($tempDir)) {
            if (!mkdir($tempDir, 0755, true) && !is_dir($tempDir)) {
                throw new Exception("Не удалось создать директорию для временных файлов: $tempDir");
            }
        }

        return $tempDir . '/' . uniqid('ReportArchive_', true) . '.zip';
    }

    /**
     * Статический метод для быстрого экспорта с настройками по умолчанию.
     *
     * Создает экземпляр класса с предустановленными зависимостями и вызывает export().
     *
     * @param array $reportData Данные отчета
     * @param string $reportTitle Заголовок отчета
     * @param array $config Конфигурация (опционально)
     * @return BinaryFileResponse Ответ с ZIP-архивом
     * @throws Exception Если экспорт не удался
     */
    public static function exportDefault(array $reportData, string $reportTitle, array $config = []): BinaryFileResponse
    {
        $validator = new ReportDataValidationService();
        $archiveService = new ArchiveService();
        $wordExporter = new WordReportExporter($validator);
        $excelExporter = new ExcelReportExporter($validator);

        return new self($archiveService, $wordExporter, $excelExporter, $config)->export($reportData, $reportTitle);
    }
}
