<?php

    require_once 'vendor/autoload.php';
    use org\majkel\dbase\Table;

    $records_meaning = ["AWIR" => "Якорь",	"AWIR_OM_OR" => "Якорь",	"AWIR_CG" => "Якорь", "AUZW" => "Статор", "AUZW_OM_OR" => "Статор", "AUZW_CG" => "Статор", 
                        "AREG" => "Регулятор напряжения", "AREG_HU_TR" => "Регулятор напряжения",	"AREG_CG" => "Регулятор напряжения", "APRO" => "Выпрямитель", 
                        "APRO_HU_TR" => "Выпрямитель", "APRO_CG" => "Выпрямитель", "ALOZP" => "Подшипник",	"ALOZP_CG" => "Подшипник",	"ALOZT" => "Подшипник",	
                        "ALOZT_CG" => "Подшипник", "ADEKP" => "Крышка передняя",	"ADEKP_CG" => "Крышка передняя", "ADEKT" => "Крышка задняя", 
                        "ADEKT_CG" => "Крышка задняя", "APOK" => "Крышка пластиковая", "APOK_CG" => "Крышка пластиковая",	 "ASZC" => "Щетки", "ASZC_EU" => "Щетки",	
                        "ATRZ" => "Щеткодержатель",	"ATRZ_HU" => "Щеткодержатель",	"ATRZ_CG" => "Щеткодержатель", "AKOL" => "Шкив",	"AKOL_CG" => "Шкив",	
                        "AREP1" => "Прочее",	"AREP1_CG" => "Прочее",	"AREP2" => "Прочее", "AREP2_CG" => "Прочее"];

    /*Connect to db*/
    $link = new mysqli('127.0.0.1:3306', 'root', 'Egich.6384483','ami_schema');
    if ($link->connect_error){
        die("Connection failed: " . $link->connect_error);
    }

    /* Open files */
    $db_alt_cz_path = './amiproject/ALT_CZ_1.DBF';

    $link->set_charset('utf8');

    /*Handlers*/
    $db_alt_cz_handler = Table::fromFile($db_alt_cz_path);

    // ass table
    $records_column_name = $db_alt_cz_handler->getFieldsNames();

    array_splice($records_column_name, 0 , 5);
    //var_dump($records_column_name);
    $records_alt_cz_table_count = $db_alt_cz_handler->getRecordsCount();
    //var_dump($records_column_name);
    for ($i = 0; $i < $records_alt_cz_table_count; $i++){
        $column_info = $db_alt_cz_handler->getRecord($i);
        
        foreach($records_column_name as $name){
          $field = $column_info[$name];
          if ($field !== ""){
            $detailCodesArr = explode(",", $field);
            for ($j = 0; $j < count($detailCodesArr); $j++){
              $detail_code = $detailCodesArr[$j];
              $datep = $column_info['DATEP']->format('d/m/Y');;
              $tmp = $column_info['TMP'];
              $hcparts = $column_info['HCPARTS'];
              $brand = $column_info['BRAND'];
              $typec = $records_meaning[$name];
              $dt_brand = (str_contains( $detail_code, '#')) ? substr( $detail_code, 0, strpos( $detail_code, '#')) : 'CARGO';
              $dt_code = (str_contains( $detail_code, '#')) ? substr( $detail_code, strpos( $detail_code, '#') + 1) :  $detail_code;
              $img = (str_contains( $detail_code, '#')) ?  $detail_code : `CARGO#${detail_code}`;
              $sql = "INSERT IGNORE INTO alt_cz(datep, tmp, hcparts, brand, typec, dt_brand, dt_code, img) VALUES
                                                                      ('${datep}',
                                                                       '${tmp}',
                                                                       '${hcparts}', 
                                                                       '${brand}',
                                                                       '${typec}',
                                                                       '${dt_brand}',
                                                                       '${dt_code}', 
                                                                       '${img}')";
              $link->query($sql);
            }
          }
        }
    }

    $link->close();

