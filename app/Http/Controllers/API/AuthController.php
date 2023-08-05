<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Helpers\ApiHelper;
use App\Models\Token;
use App\Models\User;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function createLoginToken(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $findUserdata = User::where([
            'email' => $request->email,
            'password' => $request->password
        ])->first();

        if(!$findUserdata){
            return response()->json([
                "status" => "errors",
                "errors" => "Email atau Password Salah!"
            ], 404);
        }

        try{
            $getToken = ApiHelper::CreateToken();
            $createToken = Token::create([
                'user_id' => $findUserdata['id_user'],
                'token' => 'Bearer '.$getToken
            ]);
            if($createToken){
                return response()->json([
                    'status' => 'success',
                    'token' => $createToken['token']
                ], 201);
            }else{
                throw new Exception();
            }
        }catch(\Exception){
            return response()->json([
                'status' => 'Server Error',
                'error' => 'Server Error'
            ], 500);
        }

    }

    public function logoutAndDeleteToken(Request $request){
        $tokenFE = $request->header('Authorization');
        $checkTokenAndGetId = ApiHelper::checkToken($request);
        if(isset($$checkTokenAndGetId['status'])){
            return response()->json($checkTokenAndGetId['body'], $checkTokenAndGetId['code']);
        }

        try{
            $deleteToken = Token::where('token', $tokenFE)->delete();
            if($deleteToken){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Logout berhasil, Token dihapus!'
                ], 200);
            }else{
                throw new \Exception();
            }
        }catch(\Exception){
            return response()->json([
                'status' => 'Server Error',
                'error' => 'Server Error'
            ], 500);
        }

    }
}
