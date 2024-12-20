<?php

require __DIR__ . '/vendor/autoload.php';

use App\DbfToSqlParser;

/*Connect to db*/
$parser = new DbfToSqlParser('./amiproject/ass_1/OFFICIAL.DBF');

for ($i = 0; $i < $parser->getRecordsCount(); $i++){
    $column_info = $parser->getRecord($i);
    $detail_type_name = iconv('CP866', 'UTF-8',$column_info['TYPE']);
    if ($column_info['EAC'] === null || $column_info['EAC'] === false){
        $sql = "INSERT IGNORE INTO detail_type(dt_tp_ptype, dt_tp_name, dt_tp_ngroupdeal, dt_tp_eac) VALUES
                                                                                    ('${i}',
                                                                                     '${detail_type_name}',
                                                                                     '${column_info['NGROUPDEAL']}',
                                                                                      0)";
        $parser->executeQuery($sql);
    }
    else if ($column_info['EAC'] === true){
        $sql = "INSERT IGNORE INTO detail_type(dt_tp_ptype, dt_tp_name, dt_tp_ngroupdeal, dt_tp_eac) VALUES
                                                                      ('${i}',
                                                                       '${detail_type_name}',
                                                                       '${column_info['NGROUPDEAL']}', 
                                                                       '${column_info['EAC']}')";
        $parser->executeQuery($sql);
    }
}
$parser->closeLink();

