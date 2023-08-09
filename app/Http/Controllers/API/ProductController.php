<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Shops;
use App\Models\Product;
use App\Helpers\ApiHelper;
use App\Models\Categories;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Sub_Categories;
use App\Http\Controllers\Controller;
use App\Models\Types_Sub_Categories;
use Error;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function createProduct(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }

        $validationReq = Validator::make($request->all(), [
            'category_product' => 'required|string',
            'category_type_product' => 'required|string',
            'name_product' => 'required|string|max:60', 
            'image_product' => 'required|mimes:png,jpg,jpeg,gif|max:1024',
            'desc_product' => 'required|string|max:1000',
            'price_product' => 'required|numeric|min:100',
            'stock_product' => 'required|numeric|max:9999',
            'min_order_product' => 'required|numeric|max:100'
        ]);
        if($validationReq->fails()){
            return response()->json([
                'status' => 'Bad Request',
                'message' => 'Request tidak lolos Validasi',
                'data' => $validationReq->errors()
            ], 400);
        }

        try{
            // CHECK Sub Category
            $checkCategory = Sub_Categories::where('name_sub_category', $request->input('category_product'))
                                           ->first();
            if(!isset($checkCategory)){
                return response()->json([
                    'status' => 'Bad Request',
                    'message' => 'Request tidak lolos Validasi',
                    'data' => [
                        'category_product' => 'This category is not available'
                    ]
                ], 400);
            }

            // CHECK Type Sub Category
            $checkTypeCategory = Types_Sub_Categories::where('name_type', $request->category_type_product)
                                                     ->where('sub_category_id', $checkCategory['id_sub_category'])
                                                     ->first();
            if(!isset($checkTypeCategory)){
                return response()->json([
                    'status' => 'Bad Request',
                    'message' => 'Request tidak lolos Validasi',
                    'data' => [
                        'category_type_product' => 'This type product is not available'
                    ]
                ], 400);
            }

            // CHECK Shop Id
            $checkShop = Shops::where('user_id', $checkToken)->first();
            if(!isset($checkShop)){
                return response()->json([
                    'status' => 'Forbidden',
                    'message' => 'Anda belum memiliki akses',
                ], 403);
            }
            
            $slug =  Str::slug($checkShop['name_shop'].'-'.$request->name_product.Str::random(21), "-");
            $path_image = Str::slug(Str::random(50).now(),'').'.'.$request->file('image_product')->getClientOriginalExtension();
            $insertProduct = Product::create([
                'sub_category_id' => $checkCategory['id_sub_category'],
                'shop_id' => $checkShop['id_shop'],
                'type_sub_category_id' => $checkTypeCategory['id_type_sub_category'],
                'name_product' => $request->input('name_product'),
                'desc_product' => $request->input('desc_product'),
                'price_product' => $request->input('price_product'),
                'slug_product' => $slug,
                'path_image_product' => $path_image,
                'min_buy' => $request->input('min_order_product'),
                'success_transaction' => 0,
                'duration_transaction' => 'Belum ada transaksi'
            ]);

            if($insertProduct){
                $request->file('image_product')->storeAs('product_image', $path_image, 'local');
                return response()->json([
                    'status' => 'Success',
                    'message' => '1 Product berhasil ditambahkan!' 
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