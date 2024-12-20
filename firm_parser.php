<?php

require __DIR__ . '/vendor/autoload.php';

use App\DbfToSqlParser;

/*Connect to db*/
$parser = new DbfToSqlParser('./amiproject/BAZA/FIRMS.DBF');

for ($i = 0; $i < $parser->getRecordsCount(); $i++){
    $column_info = $parser->getRecord($i);
    $firm_name = iconv('CP866', 'UTF-8',$column_info['TYPE']);
    $sql = "INSERT  INTO firm(fr_code, fr_name) VALUES ('${i}','${firm_name}')";
    $parser->executeQuery($sql);
}
print_r('first');

$parser->closeLink();

