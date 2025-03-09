<?php

namespace App\Contracts;

use Exception;

/**
 * Интерфейс для сервиса архивации файлов.
 *
 * Определяет контракт для классов, которые создают ZIP-архивы из списка файлов.
 */
interface ArchiveServiceInterface
{
    /**
     * Создает ZIP-архив из списка файлов.
     *
     * Принимает массив файлов с путями и именами, создает ZIP-архив и сохраняет его
     * по указанному пути.
     *
     * @param array $files Массив файлов, где каждый элемент содержит 'path' (путь к файлу)
     *                     и 'name' (имя файла в архиве)
     * @param string $zipFilename Путь к создаваемому ZIP-архиву
     * @return void
     * @throws Exception Если создание архива не удалось (например, ошибка доступа к файлу)
     */
    public function createZip(array $files, string $zipFilename): void;
}
