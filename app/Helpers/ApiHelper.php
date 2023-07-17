<?php

namespace App\Helpers;

class ApiHelper{
    public static function CreateToken(){
        $str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_$#@*()";
        $length_token = rand(101, 125);
        $randStr = '';
        for($i = 0; $i < $length_token; $i++){
            $randStr .= $str[rand(0, strlen($str) - 1)];
        }
        return $randStr;
    }
}