<?php

namespace App\Services;

use App\src\Exceptions\ArchiveException;
use RuntimeException;
use ZipArchive;

class ArchiveService
{
    private ZipArchive $zip;

    public function __construct()
    {
        $this->zip = new ZipArchive();
    }

    public function extract(string $archivePath, string $fileName): string
    {
        if (!file_exists($archivePath)) {
            throw new ArchiveException("Archive file not found: {$archivePath}");
        }
        $fileIndex = $this->searchInArchive($archivePath, $fileName);
        if ($fileIndex === false){
            $this->zip->close();
            throw new RuntimeException("File {$fileName} not found in archive");
        }
        $tempFile = $this->createTempPath($archivePath, $fileName, $fileIndex);
        $this->zip->close();
        return $tempFile;
    }

    protected function searchInArchive(string $archivePath, string $fileName): bool|int
    {
        var_dump($fileName);
        if ($this->zip->open($archivePath) !== true) {
            throw new RuntimeException("Cannot open archive: {$archivePath}");
        }

        $fileIndex = false;
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            if (strcasecmp(basename($this->zip->getNameIndex($i)), $fileName) === 0) {
                $fileIndex = $i;
                break;
            }
        }
        return $fileIndex;
    }

    protected function createTempPath(string $archivePath, string $fileName, int $fileIndex): string
    {
        $tempDir = sys_get_temp_dir() . '/dbf_import_' . md5($archivePath);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $tempFile = "{$tempDir}/{$fileName}";
        if (!copy("zip://{$archivePath}#{$this->zip->getNameIndex($fileIndex)}", $tempFile)) {
            throw new RuntimeException("Failed to extract {$fileName} from archive");
        }
        return $tempFile;
    }

    public function findDbfFiles(string $directory): array
    {
        $files = glob($directory . '/*.dbf') + glob($directory . '/*.DBF');
        if (empty($files)) {
            throw new ArchiveException("No DBF files found in: {$directory}");
        }
        return $files;
    }
}