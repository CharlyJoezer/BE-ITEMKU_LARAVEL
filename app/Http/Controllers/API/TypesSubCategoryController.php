<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use App\Models\Sub_Categories;
use App\Models\Types_Sub_Categories;
use App\Http\Controllers\Controller;

class TypesSubCategoryController extends Controller
{
    public function getTypeSubCategory(Request $request){
        $option = [
            'name' => $request['name_sub_category']
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
}
