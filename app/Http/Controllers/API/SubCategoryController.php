<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Sub_Categories;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubCategoryController extends Controller
{
    public function getSubCategory(Request $request){
        $option = [
            'name' => $request['name_sub_category'],
        ];
        try{
            $dataSubCategory = [];
            if($option['name'] !== null){
                $dataSubCategory = Sub_Categories::where('name_sub_category', $option['name'])
                                                 ->get(['name_sub_category as name']);

            }else{
                $dataSubCategory = Sub_Categories::all(['name_sub_category as name']);
            }


            return response()->json([
                "status" => "success",
                "message" => count($dataSubCategory)." Data ditemukan",
                "data" => $dataSubCategory
            ], 200);

        }catch(Exception){
            return response()->json([
                'status' => 'Server Error'
            ], 500);
        }
    }
}
