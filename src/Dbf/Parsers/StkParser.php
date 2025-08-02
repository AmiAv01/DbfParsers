<?php

namespace App\Dbf\Parsers;

use App\Dbf\DbfHandler;
use App\Database\Connection;
use org\majkel\dbase\Record;

class StkParser extends AbstractDbfParser
{
    private array $batch = [];

    public function __construct(Connection $db, string $filePath)
    {
        parent::__construct($db, $filePath);
    }

    protected function processRecord(Record $record): void
    {
        $this->batch[] = [
            'code' => $record['CODE'],
            'ostc' => $record['OSTC'],
            'ost' => $record['OST']
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
                "('%s', '%s', '%s')",
                $this->db->escape($item['code']),
                $this->db->escape($item['ostc']),
                $this->db->escape($item['ost'])
            );
        }

        $sql = "INSERT INTO stk (code, ostc, ost) VALUES " .
            implode(', ', $values) .
            "ON DUPLICATE KEY UPDATE ostc = VALUES(ostc), ost = VALUES(ost)";

        $this->db->query($sql);
        $this->batch = [];
    }
}
