<?php

require __DIR__ . '/vendor/autoload.php';

use App\DbfToSqlParser;

$parser = new DbfToSqlParser('./amiproject/DETAL.DBF');

for ($i = 0; $i < $parser->getRecordsCount(); $i++){
    $record = $parser->getRecord($i);
    var_dump($record);
    $sql = "INSERT IGNORE INTO price(code, zakup, opt, prod) VALUES ('${record['CODE']}','${record['ZAKUP']}','${record['OPT']}', '${record['PROD']}')";
    $parser->executeQuery($sql);
}
$parser->closeLink();