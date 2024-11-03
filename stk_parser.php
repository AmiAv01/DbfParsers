<?php

require __DIR__ . '/vendor/autoload.php';

use App\DbfToSqlParser;

$parser = new DbfToSqlParser('./amiproject/stk/stk.dbf');

for ($i = 0; $i < $parser->getRecordsCount(); $i++){
    $record = $parser->getRecord($i);
    var_dump($record);
    $sql = "INSERT IGNORE INTO stk(code, ostc, ost) VALUES ('${record['CODE']}','${record['OSTC']}','${record['OST']}')";
    $parser->executeQuery($sql);
}
$parser->closeLink();