<?php

namespace App\Http\Controllers\API;

use Error;
use Exception;
use App\Models\Shops;
use App\Models\Orders;
use App\Models\Product;
use App\Helpers\ApiHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Sub_Categories;
use App\Http\Controllers\Controller;
use App\Models\Types_Sub_Categories;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
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
                'stock_product' => $request->input('stock_product'),
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

    public function updatePriceAndStock(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }

        $validatedData = Validator::make($request->all(), [
            'product' => 'required|numeric',
            'price' => 'required|numeric|min:100|max:100000000',
            'stock' => 'required|numeric|min:0|max:9999',
        ]);
        if($validatedData->fails()){
            return response()->json([
                'status' => 'Bad Request',
                'message' => 'Request tidak lolos validasi',
                'data' => $validatedData->errors(),
            ], 400);
        }

        try{
            $checkData = Product::with(['shops' => function($query) use ($checkToken){
                $query->where('user_id', $checkToken)->select('id_shop', 'user_id');
            }])->where('id_product', $request->product)->first(['id_product', 'shop_id']);
            if(!isset($checkData) || !isset($checkData['shops'])){
                return response()->json([
                    'status' => 'Bad Request',
                    'message' => 'Terjadi Ketidakcocokan Data'
                ],400);
            }

            $updateProduct = Product::where('id_product', $request->product)->update(['price_product' => $request->price, 'stock_product' => $request->stock]);
            if($updateProduct > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data Product Berhasil diperbarui!'
                ], 200);
            }else{
                throw new Exception();
            }

        }catch(Exception){
            return response()->json([
                'status' => 'Server Error'
            ],500);
        }
    }

    public function getShopDashboardProduct(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }

        try{
            $getShop = Shops::where('user_id' , $checkToken)->first();
            if(!isset($getShop)){
                return response()->json([
                    'status' => 'Bad Request',
                    'message' => 'Terjadi Ketidakcocokan Data'
                ],400);
            }
            $search = $request->input('_search');
            if(isset($search)){
                $getProduct = Product::with(['sub_categories' => function($query){
                    $query->select('id_sub_category','name_sub_category as name');
                }, 'types_sub_categories' => function($query) {
                    $query->select('id_type_sub_category', 'name_type as name');
                }])
                ->where('shop_id', $getShop['id_shop'])
                ->where('name_product', 'like', '%'.$search.'%')
                ->get([
                        'id_product as id_p',
                        'sub_category_id',
                        'type_sub_category_id',
                        'name_product as name',
                        'price_product as harga',
                        'stock_product as stock'
                    ]);
            }else{
                $getProduct = Product::with(['sub_categories' => function($query){
                    $query->select('id_sub_category','name_sub_category as name');
                }, 'types_sub_categories' => function($query) {
                    $query->select('id_type_sub_category', 'name_type as name');
                }])
                ->where('shop_id', $getShop['id_shop'])
                ->get([
                        'id_product as id_p',
                        'sub_category_id',
                        'type_sub_category_id',
                        'name_product as name',
                        'price_product as harga',
                        'stock_product as stock'
                    ]);
            }
            
            if($getProduct){
                return response()->json([
                    'status' => 'success',
                    'message' => count($getProduct).' Produk ditemukan!',
                    'data' => $getProduct
                ],200);
            }


        }catch(Exception){
            return response()->json([
                'status' => 'Server Error'
            ], 500);
        }
    }

    public function deleteProduct(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }
        $validatedData = Validator::make($request->all(), [
            'product' => 'required|numeric'
        ]);
        if($validatedData->fails()){
            return response()->json([
                'status' => 'Bad Request',
                'message' => 'Request tidak lolos validasi',
                'data' => $validatedData->errors(),
            ], 400);
        }

        try{
            $getShopData = Shops::where('user_id', $checkToken)->first();
            if(!isset($getShopData)){
                return response()->json([
                    'status' => 'Bad Request',
                    'message' => 'Terjadi kesalahan request'
                ], 400);
            }
            $checkOrderAlready = Orders::where('product_id', $request->product)
                                       ->where('status_pesanan', '!=', 'canceled')
                                       ->get();
            if(count($checkOrderAlready) > 0){
                return response()->json([
                    'status' => 'Conflict',
                    'message' => 'Sementara anda tidak bisa menghapus Produk'
                ], 409);
            }
            $getImagePath = Product::where('id_product', $request->product)
                               ->where('shop_id', $getShopData['id_shop'])->first('path_image_product');
            $deleteProduct = Product::where('id_product', $request->product)
                            ->where('shop_id', $getShopData['id_shop'])
                            ->delete();
            if($deleteProduct){
                Storage::delete('product_image/'.$getImagePath['path_image_product']);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Produk berhasil dihapus!'
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

    public function getEditDataProduct(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }
        $validatedData = Validator::make($request->all(), [
            '_product' => 'required|numeric'
        ]);
        if($validatedData->fails()){
            return response()->json([
                'status' => 'Bad Request',
                'message' => 'Request tidak lolos validasi',
                'data' => $validatedData->errors(),
            ], 400);
        }

        try{
            $getShopData = Shops::where('user_id', $checkToken)->first();
            if(!isset($getShopData)){
                return response()->json([
                    'status' => 'Bad Request',
                    'message' => 'Terjadi kesalahan request'
                ], 400);
            }

            $getDataProduct = Product::with(['sub_categories','types_sub_categories'])
                                     ->where('id_product', $request->_product)
                                     ->where('shop_id', $getShopData['id_shop'])
                                     ->first();
            if(!isset($getDataProduct)){
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'Product tidak ditemukan'
                ],404);
            }else{
                $setBodyData = [
                    'kategori' => $getDataProduct['sub_categories']['name_sub_category'],
                    'tipe_kategori' => $getDataProduct['types_sub_categories']['name_type'],
                    'name_product' => $getDataProduct['name_product'],
                    'gambar_produk' => $getDataProduct['path_image_product'],
                    'desc' => $getDataProduct['desc_product'],
                    'harga' => $getDataProduct['price_product'],
                    'stock' => $getDataProduct['stock_product'],
                    'min_pembelian' => $getDataProduct['min_buy'],
                ];
                return response()->json([
                    'status' => 'success',
                    'data' => $setBodyData
                ], 200);                
            }
        }catch(Exception){
            return response()->json([
                'status' => 'Server Error'
            ], 500);
        }
    }

    public function updateDataProduct(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }
        $validatedData = Validator::make($request->all(), [
            '_product' => 'required|numeric',
            'gambar_produk' => $request->file('gambar_produk') ? 'required|mimes:png,jpg,jpeg,gif|max:1024' : '',
            'desc' => 'required|max:1000',
            'harga' => 'required|numeric',
            'stock' => 'required|numeric',
            'min_pembelian' => 'required|numeric',
        ]);
        if($validatedData->fails()){
            return response()->json([
                'status' => 'Bad Request',
                'message' => 'Request tidak lolos validasi',
                'data' => $validatedData->errors(),
            ], 400);
        }

        try{
            $getShopData = Shops::where('user_id', $checkToken)->first();
            if(!isset($getShopData)){
                return response()->json([
                    'status' => 'Bad Request',
                    'message' => 'Terjadi kesalahan request'
                ], 400);
            }
            $getOldDataProduct = Product::where('id_product', $request->_product)->first();
            if(!isset($getOldDataProduct)){
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }
            
            $dataUpdate = [
                'desc_product' => $request->desc,
                'price_product' => $request->harga,
                'stock_product' => $request->stock,
                'min_buy' => $request->min_pembelian,
            ];
            $path_image = '';
            if($request->file('gambar_produk') != null){
                $path_image = Str::slug(Str::random(50).now(),'').'.'.$request->file('gambar_produk')->getClientOriginalExtension();
                $dataUpdate['path_image_product'] = $path_image;
            }
            $updateProduct = Product::where('id_product', $request->_product)
                                    ->where('shop_id', $getShopData['id_shop'])
                                    ->update($dataUpdate);
            if($updateProduct > 0){
                if($request->file('gambar_produk') != null){
                    Storage::delete('product_image/'.$getOldDataProduct['path_image_product']);
                    $request->file('gambar_produk')->storeAs('product_image', $path_image, 'local');
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Berhasil update produk!'
                ], 200);
            }else{
                throw new Exception();
            }

        }catch(Exception){
            return response()->json([
                'status' => 'Server Error'
            ],500);
        }
    }

    public function getAllProduct(Request $request){
        try{
            $getAllProduct = Product::with(['sub_categories', 'shops'])->select([
                                            'id_product',
                                            'sub_category_id',
                                            'shop_id',
                                            'name_product',
                                            'price_product',
                                            'path_image_product',
                                            'slug_product',
                                            ])->paginate(10);
            $getAllProduct->getCollection()->transform(function($product){
                unset($product->id_product);
                unset($product->sub_category_id);
                unset($product->types_sub_category_id);
                unset($product->shop_id);
                $product->sub_categories->makeHidden([
                    'id_sub_category',
                    'category_id',
                    'created_at',
                    'updated_at',    
                ]);
                $product->shops->makeHidden([
                    'id_shop',
                    'user_id',
                    'created_at',
                    'updated_at',    
                    'status',    
                ]);
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
                'message' => count($getAllProduct).' Product ditemukan!',
                'pagination' => $getAllProduct,
            ]);
        }catch(Exception){
            return response()->json([
                'status' => 'Server Error'
            ]);
        }
    }

    public function getDetailProduct(Request $request){
        $validation = Validator::make($request->all(), [
            'slug' => 'required'
        ]);

        if($validation->fails()){
            return response()->json([
                'status' => 'Bad Request',
                'message' => $validation->errors(),
            ],400);
        }

        try{
            $slug = $request->input('slug');
            $getProduct = Product::with(['sub_categories','shops', 'types_sub_categories'])->where('slug_product', $slug)->get();
            if(count($getProduct) <= 0){
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'Produk tidak ditemukan!'
                ],404);
            }
            $filterData = $getProduct->map(function($product){
                                return [
                                    'gambar_produk' => $product->path_image_product,
                                    'gambar_toko' => $product->shops->path_image_shop,
                                    'nama_produk' => $product->name_product,
                                    'slug_produk' => $product->slug_product,
                                    'kategori_produk' => $product->sub_categories->name_sub_category,
                                    'tipe_kategori_produk' => $product->types_sub_categories->name_type,
                                    'harga_produk' => $product->price_product,
                                    'min_pembelian' => $product->min_buy,
                                    'transaksi_berhasil' => $product->success_transaction,
                                    'durasi_transaksi' => $product->duration_transaction,
                                    'nama_toko' => $product->shops->name_shop,
                                    'status_toko' => $product->shops->status,
                                    'desk_produk' => $product->desc_product,
                                ];
                            })[0];

            return response()->json([
                'status' => 'success',
                'message' => 'Produk ditemukan!',
                'data' => $filterData,
            ], 200);
            
        }catch(Exception){
            return response()->json([
                'status' => 'Server Error',
            ],500);
        }
    }
}
