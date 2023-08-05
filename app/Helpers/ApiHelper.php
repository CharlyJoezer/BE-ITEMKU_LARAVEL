<?php

namespace App\Helpers;

use App\Models\Token;
use Illuminate\Http\Request;

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

    public static function checkToken($request){
        $token = $request->header('Authorization');
        if(isset($token)){
            try{
                $getToken = Token::where('token', $token)->first();
                if($getToken){
                    return $getToken['user_id'];
                }
                else{
                    return  [   
                        'status' => false,
                        'code' => 403,
                        'body' => [
                            'status' => 'error',
                            'message' => 'Login Required'
                        ]
                    ];
                }
            }catch(\Exception){
                return  [   
                            'status' => false,
                            'code' => 500,
                            'body' => [
                                'status' => 'error',
                                'message' => 'Server Error'
                            ]
                        ];
            }
        }else{
            return [    
                        'status' => false,
                        'code' => 403,
                        'body' => [
                            'status' => 'error',
                            'message' => 'Login Required'
                        ]
                   ];
        }
    }
}