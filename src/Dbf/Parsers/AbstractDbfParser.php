<?php

namespace App\Dbf\Parsers;

use App\Database\Connection;
use App\Dbf\DbfHandler;

use Generator;
use org\majkel\dbase\Record;

abstract class AbstractDbfParser implements BaseParser
{
    protected Connection $db;
    protected DbfHandler $dbf;
    protected const BATCH_SIZE = 1000;

    public function __construct(Connection $db, string $filePath)
    {
        $this->db = $db;
        $this->dbf = new DbfHandler($filePath);
    }

    protected function getRecordsGenerator(): Generator
    {
        foreach ($this->dbf->getHandler() as $record) {
            yield $record;
        }
    }
    public function process(): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($this->getRecordsGenerator() as $record) {
                $this->processRecord($record);
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    abstract protected function processRecord(Record $record): void;

}