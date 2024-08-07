<?php

    require_once 'vendor/autoload.php';
    use org\majkel\dbase\Table;

    /*Connect to db*/
    $link = new mysqli('127.0.0.1:3306', 'root', 'Egich.6384483','ami_schema');
    if ($link->connect_error){
        die("Connection failed: " . $link->connect_error);
    }

    /* Open files */
    $db_ass_path = './amiproject/ass_1/ASS.DBF';

    $link->set_charset('utf8');

    /*Handlers*/
    $db_ass_handler = Table::fromFile($db_ass_path);

    // ass table
    $records_ass_table_count = $db_ass_handler->getRecordsCount();
    for ($i = 0; $i < $records_ass_table_count; $i++){
        $column_info = $db_ass_handler->getRecord($i);
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

            $link->query($sql);
            $sql = "INSERT IGNORE INTO layout_for_details(lt_dt_acode) VALUE ('${column_info['ACODE']}')";
            $link->query($sql);
    }


    $link->close();

