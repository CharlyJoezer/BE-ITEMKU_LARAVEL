<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Carts;
use App\Models\Product;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function insertCart(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }
        $validated = Validator::make($request->all(),[
            'slug' => 'required'
        ]);
        if($validated->fails()){
            return response()->json([
                'status' => 'Bad Request',
                'message' => $validated->errors(),
            ], 400);
        }

        try{
            $getProductData = Product::where('slug_product',$request->slug)->first();
            if(!isset($getProductData)){
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }
            $insertCart = Carts::create([
                'user_id' => $checkToken,
                'product_id' => $getProductData->id_product,
                'count_product' => 1
            ]);
            if($insertCart){
                return response()->json([
                    'status' => 'success',
                    'message' => '1 Produk berhasil ditambahkan',
                ], 201);
            }else{
                throw new Exception();
            }

        }catch(Exception){
            return response()->json([
                'status' => 'Server Error'
            ], 500);
        }
    }
}
