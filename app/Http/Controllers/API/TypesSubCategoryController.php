<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Sub_Categories;
use App\Http\Controllers\Controller;
use App\Models\Types_Sub_Categories;
use Illuminate\Support\Facades\Validator;

class TypesSubCategoryController extends Controller
{
    public function getTypeSubCategory(Request $request){
        $option = [
            'name' => str_replace('-',' ', $request['name_sub_category']),
        ];
        $dataTypeSubCategory = [];
        try{
            if($option['name'] !== null){
                $subCategory = Sub_Categories::where('name_sub_category', $option['name'])->first();
                if(!isset($subCategory['id_sub_category'])){
                    return response()->json([
                        'status' => 'Not Found',
                        'message' => 'Sub Kategori '.$option['name'].' tidak ditemukan!'
                    ], 404);
                }
                $dataTypeSubCategory = Types_Sub_Categories::where('sub_category_id', $subCategory['id_sub_category'])->get('name_type as name');
            }else{
                return response()->json([
                    'status' => 'Bad Request',
                    'message' => 'Parameter name_sub_category tidak ditemukan'
                ],400);
            }

            return response()->json([
                'status' => 'success',
                'message' => count($dataTypeSubCategory)." Data ditemukan",
                'data' => $dataTypeSubCategory
            ],200);

        }catch(Exception){
            return response()->json([
                'status' => 'Server Error'
            ], 500);
        }
    }

    public function getProductByTypeCategory(Request $request, $subCategory){
        if(!isset($subCategory)){
            return response()->json([
                'status' => 'Bad Request',
                'message' => 'sub category field is required!'
            ], 400);
        }
        $validation = Validator::make($request->all(), [
            'type' => 'required|string'
        ]);
        $typeCategory = $request->type;
        if($validation->fails()){
            return response()->json([
                    'status' => 'Bad Request',
                'message' => $validation->errors(),
            ]);
        }

        try{
            $subCategory = str_replace('-',' ',$subCategory);
            if($typeCategory === 'all'){
                $getSubCategory = Sub_Categories::with(['product'])->where('name_sub_category', $subCategory)->first();
                if(!isset($getSubCategory)){
                    return response()->json([
                        'status' => 'Not Found',
                        'message' => 'sub kategori tidak ditemukan!'
                    ], 404);
                }
                $getSubCategory = $getSubCategory->product->map(function($product){
                    return [
                        'nama_produk' => $product->name_product,
                        'gambar_produk' => $product->path_image_product,
                        'kategori_produk' => $product->sub_categories->name_sub_category,
                        'slug' => $product->slug_product,
                        'harga_produk' => $product->price_product,
                        'nama_toko' => $product->shops->name_shop,
                    ];
                });
                return response()->json([
                    'status' => 'success',
                    'message' => count($getSubCategory).' Produk ditemukan!',
                    'data' => $getSubCategory
                ], 200);
            }else{
                $getSubCategory = Sub_Categories::with(['types_sub_categories' => function($query) use ($typeCategory){
                    $query->where('name_type', $typeCategory)->first();
                }])->where('name_sub_category', $subCategory)->first();
                if(!isset($getSubCategory)){
                    return response()->json([
                        'status' => 'Not Found',
                        'message' => 'sub kategori tidak ditemukan!'
                    ], 404);
                }
                if(count($getSubCategory->types_sub_categories) <= 0){
                    return response()->json([
                        'status' => 'Not Found',
                        'message' => 'tipe sub kategori tidak ditemukan!'
                    ], 404);
                }
                $getProduct = Product::where([
                    'sub_category_id' => $getSubCategory->id_sub_category,
                    'type_sub_category_id' => $getSubCategory->types_sub_categories[0]['id_type_sub_category'],
                ])->paginate(10);
                $getProduct = $getProduct->getCollection()->transform(function($product){
                    return [
                        'nama_produk' => $product->name_product,
                        'gambar_produk' => $product->path_image_product,
                        'kategori_produk' => $product->sub_categories->name_sub_category,
                        'slug' => $product->slug_product,
                        'harga_produk' => $product->price_product,
                        'nama_toko' => $product->shops->name_shop,
                    ];
                });
                return response()->json([
                    'status' => 'success',
                    'message' => count($getProduct).' Produk ditemukan!',
                    'data' => $getProduct
                ], 200);
            }

        }catch(Exception){
            return response()->json([
                'status' => 'Server Error',
            ],500);
        }
    }
}
