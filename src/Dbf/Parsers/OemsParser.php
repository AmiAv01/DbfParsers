<?php

namespace App\Dbf\Parsers;

use App\Dbf\DbfHandler;
use App\Database\Connection;
use org\majkel\dbase\Record;

class OemsParser extends AbstractDbfParser
{
    private array $batch = [];
    public function __construct(Connection $db, string $filePath) {
        parent::__construct($db, $filePath);
    }

    protected function processRecord(Record $record): void
    {
        $this->batch[] = [
            'invoice' => $this->dbf->convertEncoding($record['INVOICE']),
            'parent' => $this->dbf->convertEncoding($record['PARENT']),
            'oem' => $this->dbf->convertEncoding($record['OEM']),
            'brand' => $this->dbf->convertEncoding($record['BRAND']),
            'type_rus' => $this->dbf->convertEncoding($record['TYPE_RUS'])
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
                "('%s', '%s', '%s', '%s', '%s')",
                $this->db->escape($item['invoice']),
                $this->db->escape($item['parent']),
                $this->db->escape($item['oem']),
                $this->db->escape($item['brand']),
                $this->db->escape($item['type_rus'])
            );
        }

        $sql = "INSERT INTO oems(dt_invoice, dt_parent, dt_oem, fr_code, dt_typec) 
                VALUES " . implode(', ', $values) . "
                ON DUPLICATE KEY UPDATE 
                    dt_parent = VALUES(dt_parent),
                    dt_oem = VALUES(dt_oem),
                    fr_code = VALUES(fr_code),
                    dt_typec = VALUES(dt_typec)";

        $this->db->query($sql);
        $this->batch = [];
    }
}