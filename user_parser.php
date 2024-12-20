<?php
    require_once 'vendor/autoload.php';
    use org\majkel\dbase\Table;

    $db_user_path =  './amiproject/pr/HEAP.DBF';

    $db_user_handler = Table::fromFile($db_user_path);

    $records_user_table_count = $db_user_handler->getRecordsCount();

    for ($i = 0; $i < $records_user_table_count; $i++){
        $column_info = $db_user_handler->getRecord($i);
        //var_dump(mb_detect_encoding($column_info['EMAIL'],  ['ASCII', 'UTF-8', 'ISO-8859-1','CP866', 'CP1251', 'CP1252','Windows-1252']));
        $email = iconv('Windows-1251', 'ASCII', $column_info['EMAIL']);
        $key = iconv('UTF-8', 'ASCII', 'Sel70ecTOR');
        //var_dump($key);
        $result = '';
        $result = charXor($email, $key);
        var_dump(mb_detect_encoding($result,  ['ASCII', 'UTF-8', 'ISO-8859-1','CP866', 'CP1251', 'CP1252','Windows-1252']));
        //var_dump(mb_detect_encoding($key,  ['ASCII', 'UTF-8', 'ISO-8859-1','CP866', 'CP1251', 'CP1252','Windows-1252']));
        var_dump($result);
        var_dump(iconv('Windows-1251','UTF-8',$result));
        break;
    }

    /*function charXor($string, $key){
        $x = 0;
        $result = '';
        for ($j = 0; $j < strlen($str2); $j++){
            var_dump($x);
            if ($x >= strlen($str1)){
                $x = 0;
            }
            $tmp = chr(ord($str2[$j]) ^ ord($str1[$x]));
            $tmpChr = iconv('Windows-1251', 'UTF-8', $tmp);
            var_dump($tmpChr);
            $result.=$tmpChr;
            $x++;
        }
        return $result;
    }*/

    function charXor($str, $key){
        $str_len = strlen($str);
        $key_len = strlen($key);

        for($i = 0; $i < $str_len; $i++) {
            $str[$i] = $str[$i] ^ $key[$i % $key_len];
        }

        return $str;
    }
