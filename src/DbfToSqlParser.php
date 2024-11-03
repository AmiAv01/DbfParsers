<?php

namespace App;
require_once 'vendor/autoload.php';

use mysqli;
use org\majkel\dbase\Exception;
use org\majkel\dbase\Record;
use org\majkel\dbase\Table;

final class DbfToSqlParser
{
    private mysqli $link;
    private array $databaseInfo;

    private string $tablePath;
    private Table $tableHandler;

    /**
     * @throws Exception
     */
    public function __construct(string $tablePath, array $databaseInfo = ['127.0.0.1:3306', 'root', 'Egich.6384483','ami_schema'], string $charset = 'utf8'){
        $this->link = new mysqli(...$databaseInfo);
        if ($this->link->connect_error){
            die("Connection failed: " . $this->link->connect_error);
        }
        $this->tableHandler = Table::fromFile($tablePath);
        $this->databaseInfo = $databaseInfo;
        $this->tablePath = $tablePath;
        $this->link->set_charset($charset);
    }

    /**
     * @throws Exception
     */
    public function getRecordsCount(): int {
        try {
            return $this->tableHandler->getRecordsCount();
        } catch (Exception $error) {
            throw new Exception($error->getMessage());
        }
    }


    /**
     * @throws Exception
     */
    public function getRecord(int $code): Record {
        try {
            return $this->tableHandler->getRecord($code);
        } catch (Exception $error){
            throw new Exception($error->getMessage());
        }
    }

    public function executeQuery(string $sql){
        $this->link->query($sql);
    }

    public function closeLink()
    {
        $this->link->close();
    }

}