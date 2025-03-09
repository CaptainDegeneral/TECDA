<?php

namespace App\Services;

use App\Contracts\ArchiveServiceInterface;
use Exception;
use ZipArchive;

/**
 * Сервис для создания ZIP-архивов и управления именами архивов.
 *
 * Реализует логику архивации файлов, генерации имен архивов и создания временных путей.
 *
 * @property array $config Конфигурация сервиса
 */
class ArchiveService implements ArchiveServiceInterface
{
    private array $config;

    /**
     * Конструктор класса.
     *
     * Инициализирует сервис с конфигурацией.
     *
     * @param array $config Конфигурация сервиса (опционально)
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'archiveNameFormat' => '{reportType}_{lastName}_{timestamp}.zip',
            'reportType' => 'Отчет',
            'defaultLastName' => 'Unknown',
            'tempDir' => storage_path('app/temp'),
        ], $config);
    }

    /**
     * Создает ZIP-архив из списка файлов.
     *
     * @param array $files Массив файлов с ключами 'path' (путь) и 'name' (имя в архиве)
     * @param string $zipFilename Путь к создаваемому ZIP-архиву
     * @throws Exception Если создание архива не удалось
     */
    public function createZip(array $files, string $zipFilename): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFilename, ZipArchive::CREATE) !== true) {
            throw new Exception('Не удалось создать ZIP-архив');
        }
        foreach ($files as $file) {
            if (!file_exists($file['path'])) {
                $zip->close();
                throw new Exception("Файл не существует: {$file['path']}");
            }
            $zip->addFile($file['path'], $file['name']);
        }
        $zip->close();
    }

    /**
     * Генерирует имя ZIP-архива на основе данных отчета.
     *
     * @param array $reportData Данные отчета
     * @return string Имя архива
     */
    public function generateArchiveName(array $reportData): string
    {
        $lastName = $reportData['user']['last_name'] ?? $this->config['defaultLastName'];
        $timestamp = time();

        return str_replace(
            ['{reportType}', '{lastName}', '{timestamp}'],
            [$this->config['reportType'], $lastName, $timestamp],
            $this->config['archiveNameFormat']
        );
    }

    /**
     * Создает путь для временного ZIP-файла.
     *
     * @return string Путь к временному файлу
     * @throws Exception Если не удалось создать директорию или нет прав на запись
     */
    public function createTempZipFilePath(): string
    {
        $tempDir = $this->config['tempDir'];
        if (!file_exists($tempDir) && !mkdir($tempDir, 0755, true) && !is_writable($tempDir)) {
            throw new Exception("Нет прав на запись в директорию: $tempDir");
        }
        return $tempDir . '/' . uniqid('ReportArchive_', true) . '.zip';
    }
}
