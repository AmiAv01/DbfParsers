<?php

namespace App\Dbf\Parsers;

use App\Dbf\DbfHandler;
use App\Database\Connection;
use org\majkel\dbase\Record;
use App\Services\CryptService;


class PriceParser extends AbstractDbfParser
{
    private array $batch = [];
    private CryptService $cryptService;

    public function __construct(Connection $db, string $filePath, array $config)
    {
        parent::__construct($db, $filePath);
        $this->cryptService = new CryptService($config['encryption_key']);
    }

    protected function processRecord(Record $record): void
    {
        $this->batch[] = [
            'code' => $record['CODE'],
            'opt' => $this->cryptService->crypt((string)$record['XFO']),
            'prod' => $this->cryptService->crypt((string)$record['XFR']),
            'zakup' => $this->cryptService->crypt((string)$record['XF'])
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
