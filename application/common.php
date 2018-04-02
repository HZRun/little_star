<?php

function time_format($v_time){
    $time = time();
    $sign = $time-$v_time;
    switch($sign){
        case $sign<=600:
            $r_time ='刚刚';
            break;
        case $sign>600 && $sign<=3600:
            $r_time ='一小时前';
            break;
        case $sign>3600 && $sign<86400:
            $r_time = date('H:i',$v_time);
            break;
        default:
            $r_time = date('Y/m/d',$v_time);
            break;
    }
    return $r_time;
}

function time_full_format($time){
    return date('Y-m-d H:m', $time);
}

function create_name($len){
    $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $string=time();
        for(;$len>=1;$len--)
            {
                $position=rand()%strlen($chars);
                $position2=rand()%strlen($string);
                $string=substr_replace($string,substr($chars,$position,1),$position2,0);
            }
    return $string;
}
