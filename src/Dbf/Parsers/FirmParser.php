<?php

namespace App\Dbf\Parsers;

use App\Dbf\DbfHandler;
use App\Database\Connection;
use org\majkel\dbase\Record;

class FirmParser extends AbstractDbfParser
{
    private array $batch = [];

    public function __construct(Connection $db, string $filePath) {
        parent::__construct($db, $filePath);
    }

    protected function processRecord(Record $record): void
    {
        $this->batch[] = [
            'code' => $record['CODE'],
            'name' => $this->dbf->convertEncoding($record['TYPE'])
        ];

        if (count($this->batch) >= self::BATCH_SIZE) {
            $this->flushBatch();
        }
    }

    public function process(): void
    {
        parent::process();
        $this->flushBatch();
    }

    private function flushBatch(): void
    {
        if (empty($this->batch)) {
            return;
        }

        $values = [];
        foreach ($this->batch as $item) {
            $values[] = sprintf(
                "(%d, '%s')",
                $item['code'],
                $this->db->escape($item['name'])
            );
        }

        $sql = "INSERT IGNORE INTO firm(fr_code, fr_name) VALUES " .
            implode(', ', $values);

        $this->db->query($sql);
        $this->batch = [];
    }
}