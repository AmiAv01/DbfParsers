<?php

    require_once 'vendor/autoload.php';
    use org\majkel\dbase\Table;

    /*Connect to db*/
    $link = new mysqli('127.0.0.1:3306', 'root', 'Egich.6384483','ami_schema');
    if ($link->connect_error){
        die("Connection failed: " . $link->connect_error);
    }

    /* Open files */
    $db_firm_path = './amiproject/BAZA/FIRMS.DBF';

    $link->set_charset('utf8');

    /*Handlers*/
    $db_firm_handler = Table::fromFile($db_firm_path);


    // firm table
    $records_firm_table_count = $db_firm_handler->getRecordsCount();
    for ($i = 0; $i < $records_firm_table_count; $i++){
        $column_info = $db_firm_handler->getRecord($i);
        $firm_name = iconv('CP866', 'UTF-8',$column_info['TYPE']);
        $sql = "INSERT IGNORE INTO firm(fr_code, fr_name) VALUES ('${i}','${firm_name}')";
        $link->query($sql);
    }
    print_r('first');

    $link->close();

