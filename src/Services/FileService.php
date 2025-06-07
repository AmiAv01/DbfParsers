<?php

namespace App\Services;

use App\src\Exceptions\ArchiveException;
use RuntimeException;
use ZipArchive;

class FileService
{
    public function __construct(private string $basePath) {}

    /**
     * Распаковывает архив и находит файл внутри
     *
     * @param string $archivePath Полный путь к архиву
     * @param string $filename Имя файла для поиска в архиве
     * @return string Путь к распакованному файлу
     */
    public static function search(string $fileName, string $filePath): bool
    {
        return (file_exists("{$filePath}/{$fileName}"));
    }

    public static function cleanup(): void
    {
        foreach (glob(sys_get_temp_dir() . '/dbf_import_*') as $tempDir) {
            array_map('unlink', glob("{$tempDir}/*"));
            @rmdir($tempDir);
        }
    }
}