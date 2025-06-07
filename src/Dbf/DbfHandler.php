<?php

namespace App\Dbf;

use org\majkel\dbase\Table;

class DbfHandler
{
    private Table $tableHandler;

    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("DBF file not found: {$filePath}");
        }
        $this->tableHandler = Table::fromFile($filePath);
    }

    public function getRecord(int $index): array
    {
        return (array)$this->tableHandler->getRecord($index);
    }

    public function getRecordsCount(): int
    {
        return $this->tableHandler->getRecordsCount();
    }

    public function getFields(): array
    {
        return $this->tableHandler->getFieldsNames();
    }

    public function convertEncoding(string $value, string $from = 'CP866'): string
    {
        return iconv($from, 'UTF-8//IGNORE', $value);
    }

    public function getHandler()
    {
        return $this->tableHandler;
    }
}