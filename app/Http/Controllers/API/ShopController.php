<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Shops;
use App\Models\Orders;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    public function createShop(Request $request){
        $checkToken = ApiHelper::checkToken($request);
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

    public function getDashboardShopHome(Request $request){
        $checkToken = ApiHelper::checkToken($request);
        if(!$checkToken){
            return response()->json([
                'status' => 'error',
                'error' => 'Token is not match!'
            ],404);
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
            $getDataOrder = Orders::where([
                'shop_id'=> $getDataShop['id_shop'],
            ])->get(['status_pesanan','created_at']);
            $last_30_days['amount_buyer'] = Orders::distinct('buyer_id')->count();
            
            $timeNow = Carbon::now();
            $last30DaysTime = $timeNow->copy()->subDays(30);
            
            foreach($getDataOrder as $order){
                $orderTimeCreate = $getDataOrder['created_at'];

                if($order['status'] === 'success'){
                    $orderStatusCount['success'] += 1;
                    if($orderTimeCreate >= $last30DaysTime && $orderTimeCreate <= $timeNow){
                        $last_30_days['success_orders'] += 1;
                    }

                }else if($order['status'] === 'confirmation'){
                    $orderStatusCount['confirmation'] += 1;

                }else if($order['status'] === 'process'){
                    $orderStatusCount['process'] += 1;

                }else if($order['status'] === 'canceled'){
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
}

