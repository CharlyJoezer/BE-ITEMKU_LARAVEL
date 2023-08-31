<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Helpers\ApiHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

    public function updateDataUser(Request $request){
        $checkTokenAndGetId = ApiHelper::checkToken($request);
        if(isset($checkTokenAndGetId['status'])){
            return response()->json($checkTokenAndGetId['body'], $checkTokenAndGetId['code']);
        }
        $validation = Validator::make($request->all(), [
            'username' => 'required|string',
            'email' => 'required|string',
            'profil_image' => $request->file('profil_image') ? 'required|mimes:png,jpg,jpeg|max:1024' : '',
        ]);
        if($validation->fails()){
            return response()->json([
                'status' => 'Bad Request',
                'message' => $validation->errors(),
            ], 400);
        }
    
        try{
            $getAndCheckUser = User::where('id_user', $checkTokenAndGetId)->first();
            if(!isset($getAndCheckUser)){
                return response()->json([
                    'status' => 'Unprocessable Entity',
                    'message' => 'Terjadi Kesalahan Data!'
                ], 422);
            }
            $setDataUpdate = [
                'username' => $request->input('username'),
                'email'    => $request->input('email'),
                'path_image_user' => $getAndCheckUser['path_image_user'],
            ];
            $path_image = null;
            if($request->file('profil_image') !== null){
                $path_image = Str::slug(Str::random(50).now(),'').'.'.$request->file('profil_image')->getClientOriginalExtension();
                $setDataUpdate['path_image_user'] = $path_image;
            }

            $updateDatUser = User::where('id_user', $checkTokenAndGetId)->update($setDataUpdate);
            if($updateDatUser > 0){
                if($request->file('profil_image')){
                    $request->file('profil_image')->storeAs('user_image', $path_image, 'local');
                    if($getAndCheckUser['path_image_user'] !== '/assets/users/image/default.jpg'){
                        Storage::delete('user_image/'.$getAndCheckUser['path_image_user']);
                    }
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Berhasil memperbarui profil!'
                ], 200);
            }else{
                throw new Exception;
            }
        }catch(Exception $e){
            return response()->json([
                'status' => 'Server Error',
            ], 500);
        }

    }
}
