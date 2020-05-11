<?php
    $lang = substr((string)$_SERVER['HTTP_ACCEPT_LANGUAGE'],0,5);
    echo $lang;
    if($lang == "zh-cn" or $lang == "zh-tw" or $lang == "zh-hk"){
        header("Location: zh/index.php");
        exit;
    }else{
        header("Location: en/index.php");
        exit;
    }


?>