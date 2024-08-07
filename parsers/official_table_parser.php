<?php

    require_once 'vendor/autoload.php';
    use org\majkel\dbase\Table;

    /*Connect to db*/
    $link = new mysqli('127.0.0.1:3306', 'root', 'Egich.6384483','ami_schema');
    if ($link->connect_error){
        die("Connection failed: " . $link->connect_error);
    }

    /* Open files */
    $db_official_path = './amiproject/ass_1/OFFICIAL.DBF';

    $link->set_charset('utf8');

    /*Handlers*/
    $db_official_handler = Table::fromFile($db_official_path);

    // official table
    $records_official_table_count = $db_official_handler->getRecordsCount();
    for ($i = 0; $i < $records_official_table_count; $i++){
        $column_info = $db_official_handler->getRecord($i);
        $detail_type_name = iconv('CP866', 'UTF-8',$column_info['TYPE']);
        if ($column_info['EAC'] === null || $column_info['EAC'] === false){
            //var_dump(mb_detect_encoding($detail_type_name));
            $sql = "INSERT IGNORE INTO detail_type(dt_tp_ptype, dt_tp_name, dt_tp_ngroupdeal, dt_tp_eac) VALUES
                                                                                    ('${i}',
                                                                                     '${detail_type_name}',
                                                                                     '${column_info['NGROUPDEAL']}',
                                                                                      0)";
            $link->query($sql);
        }
        else if ($column_info['EAC'] === true){
            //var_dump($column_info['EAC']);
            $sql = "INSERT IGNORE INTO detail_type(dt_tp_ptype, dt_tp_name, dt_tp_ngroupdeal, dt_tp_eac) VALUES
                                                                      ('${i}',
                                                                       '${detail_type_name}',
                                                                       '${column_info['NGROUPDEAL']}', 
                                                                       '${column_info['EAC']}')";
            $link->query($sql);
        }
    }
    print_r('second');


    $link->close();

