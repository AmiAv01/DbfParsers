<?php

require __DIR__ . '/vendor/autoload.php';

use App\DbfToSqlParser;

/*Connect to db*/
$parser = new DbfToSqlParser('./amiproject/OEMS.DBF');

// oems table
for ($i = 0; $i < $parser->getRecordsCount(); $i++){
    $column_info = $parser->getRecord($i);
    $type_rus = iconv('CP866', 'UTF-8',$column_info['TYPE_RUS']);
    $sql = "INSERT IGNORE INTO oems(dt_invoice, dt_parent, dt_oem, fr_code, dt_typec) VALUES ('${column_info['INVOICE']}', '${column_info['PARENT']}', '${column_info['OEM']}', '${column_info['BRAND']}', '${type_rus}')";
    $parser->executeQuery($sql);
}

$parser->closeLink();

