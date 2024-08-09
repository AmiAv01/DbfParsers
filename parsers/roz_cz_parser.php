<?php

require_once '../vendor/autoload.php';
use org\majkel\dbase\Table;

$records_meaning = ["RAUT" => "Соленоид",	"RAUT_CG" => "Соленоид", "RAUT1" => "Соленоид", "RAUT1_CG" => "Соленоид",
    "RBEN" => "Привод", "RBEN_GH" => "Привод", "RBEN_CG" => "Привод", "RWIR" => "Якорь", "RWIR_OM_OR" => "Якорь",
    "RWIR_CG" => "Якорь", "RUZW" => "Статор", "RUZW_OM_OR" => "Статор", "RUZW_CG" => "Статор",
    "RTRZ" => "Щеткодержатель", "RTRZ_IK" => "Щеткодержатель", "RTRZ_CG" => "Щеткодержатель", "RSZC" => "Щетки",
    "RSZC_EU" => "Щетки", "RSZC_CG" => "Щетки", "RGLO" => "Крышка передняя", "RGLO_CG" => "Крышка передняя", "RDEK" => "Крышка задняя",
    "RDEK_CG" => "Крышка задняя", "RTULP" => "Втулка", "RTULP_CG" => "Втулка", "RTULT" => "Втулка", "RTULT_CG" => "Втулка",
    "RTUL_ZES" => "Втулка",	"RTUL_CG" => "Втулка","RWID" => "Вилка", "RWID_CG" => "Вилка", "RPOD" => "Крышка", "RPOD_CG" => "Крышка",
    "RPRZ" => "Планетарная передача", "RPRZ_GH" => "Планетарная передача", "RPRZ_CG" => "Планетарная передача",
    "RBIE" => "Шестерня", "RBIE_CG" => "Шестерня", "RSPR" => "Прочее", "RSPR_CG" => "Прочее", "RLOZ" => "Подшипник",
    "RLOZ_CG" => "Подшипник", "RREP1" => "Прочее", "RREP1_CG" => "Прочее", "RREP2" => "Прочее", "RREP2_CG" => "Прочее", "RREP3" => "Прочее"];

/*Connect to db*/
$link = new mysqli('127.0.0.1:3306', 'root', 'Egich.6384483','ami_schema');
if ($link->connect_error){
    die("Connection failed: " . $link->connect_error);
}

/* Open files */
$db_roz_cz_path = '../amiproject/ROZ_CZ_1.DBF';

$link->set_charset('utf8');

/*Handlers*/
$db_roz_cz_handler = Table::fromFile($db_roz_cz_path);

// ass table
$records_column_name = $db_roz_cz_handler->getFieldsNames();

array_splice($records_column_name, 0 , 5);
$records_alt_cz_table_count = $db_roz_cz_handler->getRecordsCount();
for ($i = 0; $i < $records_alt_cz_table_count; $i++){
    $column_info = $db_roz_cz_handler->getRecord($i);
    $isEmpty = true;
    var_dump($i);
    $datep = $column_info['DATEP']->format('d/m/Y');;
    $tmp = $column_info['TMP'];
    $hcparts = $column_info['HCPARTS'];
    $brand = $column_info['BRAND'];
    foreach($records_column_name as $name){
        $field = $column_info[$name];
        if ($field !== ""){
            $detailCodesArr = explode(",", $field);
            $isEmpty = false;
            for ($j = 0; $j < count($detailCodesArr); $j++){
                $detail_code = $detailCodesArr[$j];
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
    if ($isEmpty){
        $sql = "INSERT IGNORE INTO alt_cz(datep, tmp, hcparts, brand, typec, dt_brand, dt_code, img) VALUES
                                                                      ('${datep}',
                                                                       '${tmp}',
                                                                       '${hcparts}', 
                                                                       '${brand}',
                                                                       '${typec}',
                                                                       '',
                                                                       '', 
                                                                       '')";
        $link->query($sql);
    }
}

$link->close();

