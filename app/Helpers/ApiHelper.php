<?php

namespace App\Helpers;

use App\Models\Token;

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

    public static function checkToken($token){
        if(isset($token)){
            try{
                $getToken = Token::where('token', $token)->first();
                if($getToken){
                    return $getToken['user_id'];
                }
                else{
                    return false;
                }
                
            }catch(\Exception){
                return response()->json([
                    'status' => 'Server Error',
                    'error' => 'Server Error'
                ], 500);
            }
        }else{
            return response()->json([
                'status' => 'error',
                'error' => 'Token is not found'
            ], 422);
        }
    }
}