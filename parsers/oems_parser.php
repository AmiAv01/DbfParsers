<?php

    require_once 'vendor/autoload.php';
    use org\majkel\dbase\Table;

    /*Connect to db*/
    $link = new mysqli('127.0.0.1:3306', 'root', 'Egich.6384483','ami_schema');
    if ($link->connect_error){
        die("Connection failed: " . $link->connect_error);
    }

    /* Open files */
    $db_oems_path = './amiproject/OEMS.DBF';

    $link->set_charset('utf8');

    /*Handlers*/
    $db_oems_handler = Table::fromFile($db_oems_path);

    // oems table
    $records_oems_table_count = $db_oems_handler->getRecordsCount();
    for ($i = 0; $i < $records_oems_table_count; $i++){
        $column_info = $db_oems_handler->getRecord($i);
        $type_rus = iconv('CP866', 'UTF-8',$column_info['TYPE_RUS']);
        $sql = "INSERT IGNORE INTO oems(dt_invoice, dt_parent, dt_oem, fr_code, dt_typec) VALUES ('${column_info['INVOICE']}', '${column_info['PARENT']}', '${column_info['OEM']}', '${column_info['BRAND']}', '${type_rus}')";
        $link->query($sql);
    }

    $link->close();

