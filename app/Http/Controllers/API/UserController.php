<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function getUserData(Request $request){
        $checkTokenAndGetId = ApiHelper::checkToken($request);
        if(isset($checkTokenAndGetId['status'])){
            return response()->json($checkTokenAndGetId['body'], $checkTokenAndGetId['code']);
        }

        try{
            $getUser = User::where('id_user',$checkTokenAndGetId)->with('shops')->first();
            if($getUser){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data user ditemukan!',
                    'data' => [
                        'username' => $getUser->username,
                        'email' => $getUser->email,
                        'shops' => !isset($getUser->shops->name_shop) ? null : $getUser->shops->name_shop,
                        'foto_profil' => $getUser->path_image_user,
                    ]
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
