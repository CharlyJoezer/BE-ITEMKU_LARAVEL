<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function getUserData(Request $request){
        $tokenFE = $request->header('Authorization');
        $checkTokenAndGetId = ApiHelper::checkToken($tokenFE);
        if(!$checkTokenAndGetId){
            return response()->json([
                'status' => 'error',
                'errors' => 'Mohon Login terlebih dahulu!'
            ], 403);
        }
        try{
            $getUser = User::where('id_user',$checkTokenAndGetId)->first(['username', 'email']);
            if($getUser){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data user ditemukan!',
                    'data' => $getUser
                ],200);
            }else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data user tidak ditemukan!',
                ],404);
            }
        }catch(\Exception){
            return response()->json([
                'status' => 'Server Error',
                'error' => 'Server Error'
            ], 500);
        }

    }
}
