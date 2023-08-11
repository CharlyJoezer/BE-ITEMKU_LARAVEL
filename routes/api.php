<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ImageController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ShopController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\SubCategoryController;
use App\Http\Controllers\API\TypesSubCategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::POST('/auth/login', [AuthController::class, 'createLoginToken']);
Route::POST('/auth/register', [RegisterController::class, 'processRegister']);
Route::GET('/auth/user-data', [UserController::class, 'getUserData']);
Route::POST('/auth/logout', [AuthController::class, 'logoutAndDeleteToken']);

Route::POST('/shop/create', [ShopController::class, 'createShop']);

Route::prefix('/shop/dashboard/')->group(function(){
    Route::GET('home', [ShopController::class, 'getDashboardShopHome']);
    Route::GET('pesanan/{status}', [ShopController::class, 'getDashboardPesanan']);
    Route::POST('product', [ProductController::class, 'createProduct']);

    Route::GET('profil-toko', [ShopController::class, 'getProfilShop']);
    Route::PATCH('profil-toko', [ShopController::class, 'updateProfilShop']);
});

Route::GET('/sub-categories', [SubCategoryController::class, 'getSubCategory']);
Route::GET('/types-sub-categories', [TypesSubCategoryController::class, 'getTypeSubCategory']);

Route::GET('/image/{folder_path}/{image_name}', [ImageController::class, 'getImage']);
