<?php

namespace App\Services;

use App\Contracts\ArchiveServiceInterface;
use Exception;
use ZipArchive;

class ArchiveService implements ArchiveServiceInterface
{
    /**
     * @throws Exception
     */
    public function createZip(array $files, string $zipFilename): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFilename, ZipArchive::CREATE) !== true) {
            throw new Exception('Failed to create ZIP archive');
        }
        foreach ($files as $file) {
            $zip->addFile($file['path'], $file['name']);
        }
        $zip->close();
    }
}
