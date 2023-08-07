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
            'id' => $request['id_sub_category']
        ];
        try{
            $dataSubCategory = [];
            if($option['name'] !== null && $option['id'] !== null){
                $dataSubCategory = Sub_Categories::where('id_sub_category', $option['id'])
                                                 ->where('name_sub_category', $option['name'])
                                                 ->get(['id_sub_category as id', 'name_sub_category as name']);

            }else if($option['id'] !== null){
                $dataSubCategory = Sub_Categories::where('id_sub_category', $option['id'])
                                                 ->get(['id_sub_category as id', 'name_sub_category as name']);

            }else if($option['name'] !== null){
                $dataSubCategory = Sub_Categories::where('name_sub_category', $option['name'])
                                                 ->get(['id_sub_category as id', 'name_sub_category as name']);

            }else{
                $dataSubCategory = Sub_Categories::all(['id_sub_category as id', 'name_sub_category as name']);
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
