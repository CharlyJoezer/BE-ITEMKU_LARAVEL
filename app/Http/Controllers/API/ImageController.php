<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ImageController extends Controller
{
    public function getImage($path_image, $image_name){
        try{
            $path = '';
            if($path_image === 'product'){
                $path = 'product_image/' . $image_name;
            }else if($path_image === 'shop'){
                $path = 'shop_image/' . $image_name;
            }else if($path_image === 'user'){
                $path = 'user_image/' . $image_name;
            }else{
                return response()->json([
                    'status' => 'Not Found'
                ], 404);  
            }


            if(!Storage::exists($path)){
                return response()->json([
                    'status' => 'Not Found'
                ], 404);  
            }
            $image = Storage::get($path);
            $type = Storage::mimeType($path);

            return Response::make($image, 200, ['Content-Type' => $type]);

        }catch(Exception){
            return response()->json([
                'status' => 'Server Error'
            ], 500);
        }
    }
}
