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
            $checkAlreadyInCart = Carts::where([
                'product_id' => $getProductData['id_product'],
                'user_id' => $checkToken, 
            ])->first();
            if(isset($checkAlreadyInCart)){
                $updateCart = Carts::where([
                    'product_id' => $getProductData['id_product'],
                    'user_id' => $checkToken, 
                ])->update(['count_product' => $checkAlreadyInCart['count_product'] + 1]);
                if(!($updateCart > 0)){
                    throw new Exception;
                }
            }else{
                $insertCart = Carts::create([
                    'user_id' => $checkToken,
                    'product_id' => $getProductData->id_product,
                    'count_product' => 1,
                ]);
                if(!$insertCart){
                    throw new Exception;
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => '1 Produk berhasil ditambahkan',
            ], 201);

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

    public function updateDataCart(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }
        $validation = Validator::make($request->all(), [
            'product' => 'required|string'
        ]);
        if($validation->fails()){
            return response()->json([
                'status' => 'Bad Request',
                'message' => $validation->errors()
            ], 400);
        }

        $fieldUpdate = [];
        if(isset($request->amount_product)){
            $fieldUpdate['count_product'] = $request->amount_product;
        }else{
            return response()->json([
                'status' => 'Bad Request',
                'message' => 'You must send 1 field name to update the data!',
            ], 400);
        }
        try{
            $checkProduct = Product::where('slug_product', $request->product)->first(['id_product']);
            if(!isset($checkProduct)){
                return response()->json([
                    'status' => 'Bad Request',
                    'message' => 'Error, Produk tidak ditemukan!'
                ], 400);
            }

            $checkDataCart = Carts::where([
                'user_id' => $checkToken,
                'product_id' => $checkProduct['id_product']
            ])->first();
            if(!isset($checkDataCart)){
                return response()->json([
                    'status' => 'Bad Request',
                    'message' => 'Terjadi Kesalahan'
                ], 400);
            }

            $updateCart = Carts::where([
                'user_id' => $checkToken,
                'product_id' => $checkProduct['id_product']
            ])->update($fieldUpdate);
            if($updateCart > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Update berhasil!'
                ], 200);
            }else{
                throw new Exception;
            }

            return $checkDataCart;
        }catch(Exception){
            return response()->json([
                'status' => 'Server Error'
            ], 500);
        }

    }

    public function deleteDataCart(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }
        $validation = Validator::make($request->all(), [
            'slug' => 'required|string'
        ]);
        if($validation->fails()){
            return response()->json([
                'status' => 'success',
                'message' => 'request slug tidak ditemukan!'
            ], 400);
        }
        try{
            $getDataProduct = Product::where('slug_product', $request->slug)->first(['id_product']);
            if(!isset($getDataProduct)){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Produk tidak ditemukan!'
                ], 400);
            }
            $getDataCart = Carts::where('product_id', $getDataProduct['id_product'])->first();
            if(!isset($getDataCart)){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi Kesalahan, Coba lagi!'
                ], 400);
            }

            $deleteCart = Carts::where('id_cart', $getDataCart['id_cart'])->delete();
            if($deleteCart){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Produk Keranjang berhasil dihapus!'
                ], 200);
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
