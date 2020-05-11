<?php

function randomkeys(){ 
    $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
    $key = '';
    for($i=0;$i<6;$i++)   
    {   
        $key .= $pattern{mt_rand(0,35)};    //生成php随机数   
    }
    $key = $key . '.png';
    return $key;   

}
?>