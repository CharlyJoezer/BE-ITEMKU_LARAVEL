<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiHelper;
use App\Models\Token;
use App\Models\User;
use Exception;
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
                'token' => $getToken
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
}
