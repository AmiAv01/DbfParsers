<?php

namespace App\Dbf\Parsers;

use App\Dbf\DbfHandler;
use App\Database\Connection;
use org\majkel\dbase\Record;

class PriceParser extends AbstractDbfParser
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
            'zakup' => $record['ZAKUP'],
            'opt' => $record['OPT'],
            'prod' => $record['PROD']
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
                "('%s', '%s', '%s', '%s')",
                $this->db->escape($item['code']),
                $this->db->escape($item['zakup']),
                $this->db->escape($item['opt']),
                $this->db->escape($item['prod'])
            );
        }

        $sql = "INSERT INTO price (code, zakup, opt, prod) VALUES " .
            implode(', ', $values) .
            "ON DUPLICATE KEY UPDATE zakup = VALUES(zakup), opt = VALUES(opt), prod = VALUES(prod)";

        $this->db->query($sql);
        $this->batch = [];
    }
}
