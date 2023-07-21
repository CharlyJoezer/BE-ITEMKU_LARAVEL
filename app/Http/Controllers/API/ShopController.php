<?php

namespace App\Http\Controllers\API;

use App\Models\Shops;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    public function createShop(Request $request){
        $token = $request->header('Authorization');
        $checkToken = ApiHelper::checkToken($token);
        if(!$checkToken){
            return response()->json([
                'status' => 'error',
                'error' => 'Token is not match!'
            ],404);
        }

        $validator = Validator::make($request->all(), [
            'name_shop' => 'required|unique:shops,name_shop'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $setInsertData = [
            'user_id' => $checkToken,
            'name_shop' => $request->name_shop,
            'path_image_shop' => '/assets/shop/profil_default.png'
        ];

        try{
            $checkAlreadyHaveShop = Shops::where('user_id', $checkToken)->get();
            if(count($checkAlreadyHaveShop) <= 0){
                $createShop = Shops::create($setInsertData);
                if($createShop){
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Toko Berhasil dibuat!'
                    ], 201);
                }else{
                    throw new \Exception();
                }
            }else{
                return response()->json([
                    'status' => 'Conflict',
                    'message' => 'Kamu hanya bisa memiliki 1 Toko'
                ], 409);
            }
        }catch(\Exception){
            return response()->json([
                'status' => 'Server Error',
                'error' => 'Server Error'
            ], 500);
        }
    }
}


