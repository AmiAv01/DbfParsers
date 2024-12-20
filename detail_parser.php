<?php

require __DIR__ . '/vendor/autoload.php';

use App\DbfToSqlParser;

/*Connect to db*/
$parser = new DbfToSqlParser('./amiproject/ass_1/ASS.DBF');
for ($i = 0; $i < $parser->getRecordsCount(); $i++){
    $column_info = $parser->getRecord($i);
    $detail_comment = iconv('CP866', 'UTF-8',$column_info['COMMENT']);
    $detail_type = iconv('CP866', 'UTF-8', $column_info['TYPE']);
    $detail_typec = iconv('CP866', 'UTF-8',$column_info['TYPEC']);
    var_dump($detail_type);
    $sql = "INSERT IGNORE INTO detail(dt_id, dt_code, dt_extcode, dt_extname, dt_type, dt_comment, dt_foto, dt_invoice, dt_netto, dt_oem, dt_baza, 
                dt_cena, dt_prod, dt_ost, dt_ostc, dt_typec, dt_bp, dt_cargo, dt_e, dt_hs, dt_datep, fr_code, dt_tp_ptype, 
                lt_dt_acode) VALUES ('$i',
                                     '${column_info['CODE']}',
                                     '${column_info['EXTCODE']}',
                                     '${column_info['EXTNAME']}',
                                     '${detail_type}',
                                     '${detail_comment}',
                                     '${column_info['FOTO']}',
                                     '${column_info['INVOICE']}',
                                     '${column_info['NETTO']}',
                                     '${column_info['OEM']}',
                                     '${column_info['BAZA']}',
                                     '${column_info['CENA']}',
                                     '${column_info['PROD']}',
                                     '${column_info['OST']}',
                                     '${column_info['OSTC']}',
                                     '${detail_typec}',
                                     '${column_info['BP']}',
                                     '${column_info['CARGO']}',
                                     '${column_info['E']}',
                                     '${column_info['HS']}',
                                     NULL,
                                     '${column_info['FIRMS']}',
                                     '${column_info['PTYPE']}',
                                     '${column_info['ACODE']}')";

    $parser->executeQuery($sql);
    $sql = "INSERT IGNORE INTO layout_for_details(lt_dt_acode) VALUE ('${column_info['ACODE']}')";
    $parser->executeQuery($sql);
}
$parser->closeLink();

