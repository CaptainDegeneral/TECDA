<?php

namespace App\Exports;

use App\Contracts\ArchiveServiceInterface;
use App\Services\ArchiveService;
use App\Services\ReportDataValidationService;
use App\Services\TitleCleanerService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Exception;

/**
 * Класс для экспорта отчетов в различные форматы с последующей архивацией в ZIP.
 *
 * Координирует создание отчетов через массив экспортеров, их упаковку в ZIP-архив
 * и предоставление пользователю возможности скачать результат.
 *
 * @property ArchiveServiceInterface $archiveService Сервис для работы с архивами
 * @property array $exporters Массив экспортеров отчетов
 * @property TitleCleanerService $titleCleaner Сервис для очистки заголовков
 * @property array $config Конфигурация экспорта
 */
class ReportExporter
{
    private ArchiveServiceInterface $archiveService;
    private array $exporters;
    private TitleCleanerService $titleCleaner;
    private array $config;

    /**
     * Конструктор класса.
     *
     * Инициализирует объект с зависимостями и конфигурацией.
     *
     * @param ArchiveServiceInterface $archiveService Сервис для создания ZIP-архивов
     * @param array $exporters Массив объектов, реализующих ReportExporterInterface
     * @param TitleCleanerService $titleCleaner Сервис для очистки заголовков отчетов
     * @param array $config Конфигурация экспорта (опционально)
     */
    public function __construct(
        ArchiveServiceInterface $archiveService,
        array $exporters,
        TitleCleanerService $titleCleaner,
        array $config = []
    ) {
        $this->archiveService = $archiveService;
        $this->exporters = $exporters;
        $this->titleCleaner = $titleCleaner;
        $this->config = array_merge([
            'formats' => [
                WordReportExporter::class => 'docx',
                ExcelReportExporter::class => 'xlsx',
            ],
        ], $config);
    }

    /**
     * Экспортирует данные отчета в заданные форматы, архивирует их в ZIP и возвращает файл для скачивания.
     *
     * Создает временные файлы отчетов через массив экспортеров, упаковывает их в ZIP-архив
     * и возвращает HTTP-ответ для скачивания. После отправки файл удаляется.
     *
     * @param array $reportData Данные отчета для экспорта
     * @param string $reportTitle Заголовок отчета, используемый для именования файлов
     * @return BinaryFileResponse Ответ с ZIP-архивом для скачивания
     * @throws Exception Если произошла ошибка во время экспорта или архивации
     */
    public function export(array $reportData, string $reportTitle): BinaryFileResponse
    {
        try {
            $files = [];
            foreach ($this->exporters as $exporter) {
                $filePath = $exporter->export($reportData);
                $class = get_class($exporter);
                $extension = $this->config['formats'][$class] ?? 'tmp';
                $files[] = [
                    'path' => $filePath,
                    'name' => $this->titleCleaner->clean($reportTitle) . '.' . $extension
                ];
            }

            $zipFilename = $this->archiveService->createTempZipFilePath();
            $this->archiveService->createZip($files, $zipFilename);
            $archiveName = $this->archiveService->generateArchiveName($reportData);

            return response()->download($zipFilename, $archiveName)->deleteFileAfterSend();
        } catch (Exception $e) {
            throw new Exception('Ошибка при экспорте отчета: ' . $e->getMessage(), 0, $e);
        }
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
        $titleCleaner = new TitleCleanerService();

        $exporters = [
            new WordReportExporter($validator),
            new ExcelReportExporter($validator),
        ];

        return new self($archiveService, $exporters, $titleCleaner, $config)->export($reportData, $reportTitle);
    }
}
