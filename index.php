<?php

require __DIR__ . '/vendor/autoload.php';

use App\Database\Connection;
use App\Dbf\Parsers\ParserContext;
use App\Dbf\Parsers\ParserStrategyFactory;
use App\Services\FileService;
use App\Services\ArchiveService;

$config = require('config.php');

// Проверка аргументов
if ($argc < 3) {
    echo "Usage: php import.php <filename> <archive_path>\n";
    echo "Example: php import.php ASS.DBF /path/to/archive.zip\n";
    exit(1);
}

$filename = $argv[1];
$archivePath = $argv[2];

try {
    $db = new Connection($config['db']);
    $archiveService = new ArchiveService();

    $dbFile = (FileService::search($filename, $config['dir_path'])) ? "{$config['dir_path']}/{$filename}" : $archiveService->extract($archivePath, $filename);
    $parser = ParserStrategyFactory::create($dbFile, $db, $config);
    $parserContext = new ParserContext($parser);
    $parserContext->execute($filename, $archivePath);
    FileService::cleanup();

    exit(0);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}