<?php

namespace App\Services;

/**
 * Сервис для очистки заголовков отчетов.
 *
 * Преобразует заголовки в безопасный формат для использования в именах файлов,
 * удаляя специальные символы и форматируя даты.
 */
class TitleCleanerService
{
    /**
     * Очищает заголовок отчета для безопасного использования в именах файлов.
     *
     * Удаляет специальные символы, заменяет пробелы на подчеркивания и форматирует даты.
     *
     * @param string $title Исходный заголовок отчета
     * @return string Очищенный заголовок
     */
    public function clean(string $title): string
    {
        $cleanTitle = trim($title);
        $cleanTitle = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $cleanTitle);
        $cleanTitle = preg_replace('/\s+/u', '_', $cleanTitle);

        if (preg_match('/_от_(\d{2}_\d{2}_\d{4})/', $cleanTitle, $matches)) {
            $date = str_replace('_', '-', $matches[1]);
            $cleanTitle = str_replace($matches[0], '_от_' . $date, $cleanTitle);
        }

        return $cleanTitle;
    }
}
