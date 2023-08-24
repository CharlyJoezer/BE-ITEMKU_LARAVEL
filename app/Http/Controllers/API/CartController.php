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

    public function getDataCart(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }
        try{
            $getCartData = Carts::with(['products' => [
                'sub_categories',
                'shops',
            ]])
            ->where('user_id', $checkToken)
            ->get();

            if($getCartData){
                $filterData = $getCartData->map(function($cart){
                    return [
                        'path_image_product' => $cart->products->path_image_product,
                        'nama_produk' => $cart->products->name_product,
                        'kategori_produk' => $cart->products->sub_categories->name_sub_category,
                        'harga_produk' => $cart->products->price_product,
                        'stok_produk' => $cart->products->stock_product,
                        'slug_produk' => $cart->products->slug_product,
                        'nama_toko' => $cart->products->shops->name_shop,
                        'jumlah_produk' => $cart->count_product,
                    ];
                });
                return response()->json([
                    'status' => 'success',
                    'message' => count($getCartData).' Data ditemukan!',
                    'data' => $filterData,
                ],200);
            }else{
                throw new Exception();
            }
        }catch(Exception){
            return response()->json([
                'status' => 'Server Error',
            ], 500);
        }
    }
}
