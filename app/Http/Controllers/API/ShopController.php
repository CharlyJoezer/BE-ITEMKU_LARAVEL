<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Shops;
use App\Models\Orders;
use App\Helpers\ApiHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    public function createShop(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
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
            'path_image_shop' => '/assets/shop/profil.png'
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

    public function getDashboardShopHome(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }
        $id_user = $checkToken;
        
        $orderStatusCount = [
            'success' => 0,
            'confirmation' => 0,
            'process' => 0,
            'canceled' => 0
        ];
        $last_30_days = [
            'amount_buyer' => 0,
            'success_orders' => 0,
            'canceled_orders' => 0,
        ];
        try{
            $getDataShop = Shops::where('user_id', $id_user)->first();
            if(!isset($getDataShop)){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Toko tidak ditemukan'
                ],404);
            }

            $getDataOrder = Orders::where([
                'shop_id'=> $getDataShop['id_shop'],
            ])->get(['status_pesanan','created_at']);
            $last_30_days['amount_buyer'] = Orders::where('shop_id', $getDataShop['id_shop'])->distinct('buyer_id')->count();
            
            $timeNow = Carbon::now();
            $last30DaysTime = $timeNow->copy()->subDays(30);
            
            foreach($getDataOrder as $order){
                $orderTimeCreate = $order['created_at'];

                if($order['status_pesanan'] === 'success'){
                    $orderStatusCount['success'] += 1;
                    if($orderTimeCreate >= $last30DaysTime && $orderTimeCreate <= $timeNow){
                        $last_30_days['success_orders'] += 1;
                    }

                }else if($order['status_pesanan'] === 'confirmation'){
                    $orderStatusCount['confirmation'] += 1;

                }else if($order['status_pesanan'] === 'process'){
                    $orderStatusCount['process'] += 1;

                }else if($order['status_pesanan'] === 'canceled'){
                    $orderStatusCount['canceled'] += 1;
                    if($orderTimeCreate >= $last30DaysTime && $orderTimeCreate <= $timeNow){
                        $last_30_days['canceled_orders'] += 1;
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'name_shop' => $getDataShop['name_shop'],
                    'status' => $getDataShop['status'],
                    'image_shop' => $getDataShop['path_image_shop'],
                    'stats_orders' => [
                        'order_success' => $orderStatusCount['success'],
                        'order_confirmation' => $orderStatusCount['confirmation'],
                        'order_process' => $orderStatusCount['process'],
                        'order_canceled' => $orderStatusCount['canceled'],
                    ],
                    'last_30_days_stats_order' => $last_30_days,
                ],
            ], 200);


        }catch(Exception){
            return response()->json([
                'status' => 'Server Error',
            ], 500);
        }
    }

    public function getDashboardPesanan(Request $request, $status){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }

        try{
            $getDataShop = Shops::where('user_id', $checkToken)->first();
            if(!isset($getDataShop)){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Toko tidak ditemukan'
                ],404);
            }
            
            $statusOrder = null; 

            if($status === 'perlu-diproses'){
                $statusOrder = 'process';
                
            }else if($status === 'menunggu-konfirmasi'){
                $statusOrder = 'confirmation';

            }else if($status === 'pesanan-selesai'){
                $statusOrder = 'success';

            }else if($status === 'pesanan-dibatalkan'){
                $statusOrder = 'canceled';

            }else{
                return response()->json([
                    'status' => 'Not Found'
                ], 404);
            }

            $getDataOrder = Orders::where([
                'shop_id'=> $getDataShop['id_shop'],
                'status_pesanan' => $statusOrder
            ])->with([
                'users' => function($query){
                    $query->select('id_user','username');
                },
                'products' => function($query){
                    $query->select('id_product', 'sub_category_id', 'name_product','price_product');
                },
                'products.sub_categories' => function($query){
                    $query->select('id_sub_category', 'name_sub_category');
                }
            ])->get(['product_id','buyer_id','shop_id','order_code','amount','status_pesanan','created_at']);

            
            return response()->json([
                'status' => 'success',
                'message' => (count($getDataOrder) <= 0) ? 'Pesanan Tidak Ditemukan!' : count($getDataOrder).' Pesanan ditemukan',
                'order_status' => $status,
                'data' => $getDataOrder
            ],200);
            

        }catch(Exception){
            return response()->json([
                'status' => 'Server Error'
            ],500);
        }
    }

    public function updateProfilShop(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }
        $validateData = Validator::make($request->all(),[
            'image_shop' => $request->file('image_shop') ? 'required|mimes:png,jpg,jpeg,gif|max:1024' : '' 
        ]);
        if($validateData->fails()){
            return response()->json([
                'status' => 'Bad Request',
                'message' => 'Request tidak lolos validasi',
                'data' => $validateData->errors()
            ], 400);
        }

        $path_image = null;
        if($request->file('image_shop')){
            $path_image = Str::slug(Str::random(50).now(),'').'.'.$request->file('image_shop')->getClientOriginalExtension();
        }
        try{
            $getDataShop = Shops::where('user_id', $checkToken)->first();
            $updateShop = Shops::where('user_id', $checkToken)
                          ->update(['path_image_shop' => isset($path_image) ? $path_image : '/assets/shop/profil.png']);

            if($updateShop > 0){
                if($path_image !== null){
                    if($getDataShop['path_image_shop'] !== '/assets/shop/profil.png'){
                        Storage::delete('shop_image/'.$getDataShop['path_image_shop']);
                    }
                    $request->file('image_shop')->storeAs('shop_image', $path_image, 'local');
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data Profil berhasil diperbarui',
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

    public function getProfilShop(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }

        try{
            $getShops = Shops::where('user_id', $checkToken)->first(['name_shop as name', 'path_image_shop as image', 'status']);
            if($getShops){
                return response()->json([
                    'status' => 'success',
                    'data' => $getShops,
                ], 200);
            }else{
                throw new Exception;
            }
        }catch(Exception){
            return response()->json([
                'status' => 'Server Error'
            ], 500);
        }
    }

    public function setStatusShop(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(isset($checkToken['status'])){
            return response()->json($checkToken['body'], $checkToken['code']);
        }

        $validateData = Validator::make($request->all(), [
            'status' => 'required|string|max:5'
        ]);
        if($validateData->fails()){
            return response()->json([
                'status' => 'Bad Request',
                'message' => 'Request tidak lolos validasi',
                'data' => $validateData->errors(),
            ], 400);
        }

        try{
            $getShopData = Shops::where('user_id', $checkToken)->first();
            if(!isset($getShopData)){
                return response()->json([
                    'status' => 'Bad Request',
                    'message' => 'Kamu belum memiliki Toko'
                ], 400);    
            }

            $status = ($request->status == 'true') ? 'Tutup' : 'Buka';
            $updateStatus = Shops::where('user_id', $checkToken)
                                 ->update([
                                    'status' => $status
                                 ]);
            if($updateStatus > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Status Toko '.$status,
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


