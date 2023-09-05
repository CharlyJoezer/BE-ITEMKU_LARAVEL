<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Exception;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function indexCategories(Request $request){
        // try{
            $finalData = [];
            $nameCategory = $request->category_name;
            $filterNameCategories = str_replace('-',' ',$nameCategory);
            if($nameCategory === null){
                $finalData = Categories::all()->map(function($categories){
                    return [
                        'name_category' => $categories->name_category, 
                    ];
                });
            }else{
                $data = Categories::with(['sub_categories' => function($query){
                    $query->select('id_sub_category', 'category_id','name_sub_category as name', 'path_image_sub_categories as image');
                }])
                ->where('name_category', $filterNameCategories)
                ->get();
                if(count($data) > 0){
                    $finalData = $data->map(function($categories){
                        $categories->makeHidden([
                            'id_category',
                            'created_at',
                            'updated_at',
                        ]);
                        $categories->sub_categories->makeHidden([
                            'id_sub_category',
                            'category_id',
                            'created_at',
                            'updated_at',
                        ]);
                        return [
                            'name_category' => $categories->name_category,
                            'list_sub_category' => $categories->sub_categories,
                        ];
                    })[0];
                }else{
                    return response()->json([
                        'status' => 'Not Found',
                        'message' => 'The Category is not found'
                    ], 404);
                }
            }
            return response()->json([
                'status' => 'success',
                'data' =>  $finalData,
            ], 200);
        // }catch(Exception){
        //     return response()->json([
        //         'status' => 'Server Error'
        //     ], 500);
        // }
    }
}
